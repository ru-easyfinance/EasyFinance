<?php
    echo link_to( $billing_transaction->getServiceName(), "/services/" . $billing_transaction->getServiceId() . "/edit" );
?>