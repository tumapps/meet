<?php

namespace helpers\traits;


trait Status
{
    public static function badge($code)
    {
        switch ($code) {
            case 0:
                $status = ['label' => 'DELETED', 'theme' => 'danger',];
                break;
            case 1:
                $status = ['label' => 'ACTIVE', 'theme' => 'success',];
                break;
            case 2:
                $status = ['label' => 'CONFIRMED', 'theme' => 'primary',];
                break;
            case 3:
                $status = ['label' => 'RESCHEDULE', 'theme' => 'light',];
                break;
            case 4:
                $status = ['label' => 'CANCELLED', 'theme' => 'secondary',];
                break;
            case 5:
                $status = ['label' => 'RESCHEDULED', 'theme' => 'info',];
                break;
            case 9:
                $status = ['label' => 'MISSED', 'theme' => 'warning',];
                break;
            case 10:
                $status = ['label' => 'ATTENDED', 'theme' => 'dark',];
                break;
            default:
                $status = ['label' => 'Unknown', 'theme' => 'dark',];
        }
        // return '<span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-' . $status['theme'] . '-light text-' . $status['theme'] . '">' . $status['label'] . '</span>';
        return [
            'label' => $status['label'],
            'theme' => $status['theme'],
        ];
    }
}
