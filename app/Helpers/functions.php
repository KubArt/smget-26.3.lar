<?php


if (! function_exists('format_phoneToInt')) {
    function format_phoneToInt($phone = ''){
        //echo $phone;

        $phone = preg_replace('/[^0-9]/', '', $phone); // вернет 79851111111

        if (strlen($phone) != 11 and ($phone[0] != '7' or $phone[0] != '8')) {
            return $phone;//FALSE;
        }

        $phone_number['dialcode'] = substr($phone, 0, 1);
        $phone_number['code']  = substr($phone, 1, 3);
        $phone_number['phone'] = substr($phone, -7);
        $phone_number['phone_arr'][] = substr($phone_number['phone'], 0, 3);
        $phone_number['phone_arr'][] = substr($phone_number['phone'], 3, 2);
        $phone_number['phone_arr'][] = substr($phone_number['phone'], 5, 2);

        //$format_phone = '+' . $phone_number['dialcode'] . ' ('. $phone_number['code'] .') ' . implode('-', $phone_number['phone_arr']);
        $format_phone = "7" .  $phone_number['code']  . implode('', $phone_number['phone_arr']);

        return $format_phone;
    }
}
