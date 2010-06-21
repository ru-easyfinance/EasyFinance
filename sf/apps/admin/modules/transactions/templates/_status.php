<?php
    switch ( $billing_transaction->getStatus() ) {

    	case 1: echo '<span style="color:green">Оплачено</span>';
    	        break;

    	case 2: echo '<span style="color:red">Ошибка</span>';
                break;

    	default: echo "Не оплачено";
    	         break;

    }
?>