<?php
    echo link_to( $service_subscription->getService()->getName(), "/admin/services/" . $service_subscription->getServiceId() . "/edit" );
?>