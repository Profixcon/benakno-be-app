<?php

if (!function_exists('ej')) {
    function ej($arr)
    {
        echo json_encode($arr);

        exit();
    }


    /**
     * Get the related table name based on the parent table name.
     *
     * @param string $parentTable
     * @return string
     */
    function getRelatedTable($parentTable)
    {
        switch ($parentTable) {
            case 'customer':
                return 'access_user';
            case 'mitra':
                return 'access_mitra';
            case 'services':
                return 'm_services';
            // Add more cases for other parent tables and related tables if needed
            default:
                return '';
        }
    }

    /**
     * Get the parent table name based on the model type input.
     *
     * @param string $modelType
     * @return string
     */
    function getParentTable($modelType)
    {
        switch ($modelType) {
            case 'AuthModel':
                return 'access_auth';
            case 'ServiceModel':
                return 't_services';
            case 'VehicleModel':
                return 't_vehicle';
            // Add more cases for other model types if needed
            default:
                return '';
        }
    }

    /**
     * Get the dynamic user_id column name for the related table.
     *
     * @param string $relatedTable
     * @return string
     */
    function getRelatedColumn($relatedTable)
    {
        switch ($relatedTable) {
            case 'access_customer':
                return 'user_id';
            case 'access_services':
                return 'user_id';
            case 'm_services':
                return 'id';
            // Add more cases for other related tables if needed
            default:
                return 'user_id';
        }
    }
}
