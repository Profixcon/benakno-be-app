<?php

namespace App\Traits;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use GuzzleHttp\Client;

trait FirebaseTrait
{
    // init firebase
    public function initFirebaseCustomer()
    {
        $factory = (new Factory)->withServiceAccount(base_path() . '/firebase-config-customer.json');
        $messaging = $factory->createMessaging();
        return $messaging;
    }

    // init firebase
    public function initFirebaseMitra()
    {
        $factory = (new Factory)->withServiceAccount(base_path() . '/firebase-config-mitra.json');
        $messaging = $factory->createMessaging();
        return $messaging;
    }

    public function sendNotification($deviceToken, $title, $body, $data, $app = 'customer')
    {
        $required = [
            'title' => $title,
            'body' => $body,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        ];
        $data = array_merge($required, $data);

        $messaging = $this->initFirebaseCustomer();
        if ($app == 'mitra') {
            $messaging = $this->initFirebaseMitra();
        }

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withData($data);
        $messaging->send($message);
    }

    public function sendNotificationToTopic($topic, $title, $body, $data, $app = 'customer')
    {
        $required = [
            'title' => $title,
            'body' => $body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        $result = array_merge($required, $data);

        $messaging = $this->initFirebaseCustomer();
        if ($app == 'mitra') {
            $messaging = $this->initFirebaseMitra();
        }

        $message = CloudMessage::withTarget('topic', $topic)->withNotification($result) // optional
        ->withData($result);

        return $messaging->send($message);
    }

    public function sendNotificationToCondition($condition, $title, $body, $data, $app = 'customer')
    {
        $required = [
            'title' => $title,
            'body' => $body,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        ];
        $data = array_merge($required, $data);

        $messaging = $this->initFirebaseCustomer();
        if ($app == 'mitra') {
            $messaging = $this->initFirebaseMitra();
        }
        $message = CloudMessage::withTarget('condition', $condition)
            ->withData($data);
        $messaging->send($message);
    }

    public function subscribeToTopic($topic, $deviceToken, $app = 'customer')
    {

        $messaging = $this->initFirebaseCustomer();
        if ($app == 'mitra') {
            $messaging = $this->initFirebaseMitra();
        }

        $messaging->subscribeToTopic($topic, $deviceToken);
    }

    public function unsubscribeFromTopic($topic, $deviceToken, $app = 'customer')
    {

        $messaging = $this->initFirebaseCustomer();
        if ($app == 'mitra') {
            $messaging = $this->initFirebaseMitra();
        }

        $messaging->unsubscribeFromTopic($topic, $deviceToken);
    }

    /**
     * getSubscribedTopics
     *
     * @param mixed $deviceToken
     * @return void
     */
    public function getSubscribedTopics($deviceToken, $app = 'customer')
    {

        $messaging = $this->initFirebaseCustomer();
        if ($app == 'mitra') {
            $messaging = $this->initFirebaseMitra();
        }

        $appInstance = $messaging->getAppInstance($deviceToken);

        $subscriptions = $appInstance->topicSubscriptions();

        // check if the device is subscribed to any topics
        if ($subscriptions->count() > 0) {
            // iterate over the subscriptions
            $topic = [];
            foreach ($subscriptions as $subscription) {
                // get the topic name
                $topic[] = $subscription->topic();
            }
            $this->ej($topic);
        } else {
            echo "No subscriptions found\n";
        }
    }

    public function generateDynamicLink($stringParam = '')
    {

        $api_key = env('FIREBASE_KEY');

        // Set up the Firebase Dynamic Links API endpoint
        $apiEndpoint = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key={$api_key}";

        // Create the Guzzle HTTP client
        $client = new Client();

        // Build the request body
        $body = [
            'dynamicLinkInfo' => [
                'domainUriPrefix' => 'https://app-ikanas.venturo.in',
                'link' => 'YOUR_TARGET_LINK',
                'androidInfo' => [
                    'androidPackageName' => 'com.benakno',
                    'androidFallbackLink' => 'https://benakno.com',
                    'androidLink' => 'YOUR_ANDROID_APP_LINK',
                    'androidAppIndexingLink' => 'YOUR_ANDROID_APP_INDEXING_LINK',
                ]
            ],
            'suffix' => [
                'option' => 'SHORT',
            ],
        ];

        // Add the custom string parameter to the target link
        $targetLink = $body['dynamicLinkInfo']['link'] . '?stringParam=' . $stringParam;
        $body['dynamicLinkInfo']['link'] = $targetLink;

        // Send the API request
        $response = $client->request('POST', $apiEndpoint, [
            'json' => $body,
        ]);

        // Decode the response JSON
        $data = json_decode($response->getBody(), true);

        // Return the short link URL
        return $data['shortLink'];
    }
}
