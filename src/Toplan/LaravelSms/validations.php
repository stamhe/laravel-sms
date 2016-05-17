<?php

use Toplan\Sms\LaravelSmsException;

Validator::extend('zh_mobile', function ($attribute, $value, $parameters) {
    return preg_match('/^(\+?0?86\-?)?((13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7})$/', $value);
});

Validator::extend('confirm_mobile_not_change', function ($attribute, $value, $parameters) {
    $token = isset($parameters[0]) ? $parameters[0] : null;
    $smsData = SmsManager::retrieveSentInfo($token);

    return $smsData && $smsData['mobile'] === $value;
});

Validator::extend('verify_code', function ($attribute, $value, $parameters) {
    $token = isset($parameters[0]) ? $parameters[0] : null;
    $smsData = SmsManager::retrieveSentInfo($token);

    return $smsData && $smsData['deadline'] >= time() && $smsData['code'] === $value;
});

$fields = SmsManager::getVerifiableFields();
foreach ($fields as $field) {
    Validator::extend('confirm_' . $field . '_rule', function ($attribute, $value, $parameters) use ($field) {
        if (!isset($parameters[0])) {
            throw new LaravelSmsException('Please give validator rule [confirm_' . $field . '_rule] at least one parameter!');
        }
        $token = isset($parameters[1]) ? $parameters[1] : null;
        $smsData = SmsManager::retrieveSentInfo($token);

        return $smsData && $smsData['usedRule'][$field] === $parameters[0];
    });
}
