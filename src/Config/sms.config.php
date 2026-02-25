<?php
    
declare(strict_types=1);
    
/**
 * SMS Configuration
 */
return function (string $mode): array {
    
    if ($mode === 'PROD' || $mode === 'DEV') {
        return [
            'api_base_url' => 'http://sms.moplet.com/api/',
            'authkey' => '2372AzSgzRaq6092318dP43', //TODO: Acumen Care authkey
            'sender' => 'ACUMEN',
            'route' => 4,
            'country' => 91,
            ];
    }
    
    throw new RuntimeException("Invalid APP mode: {$mode}");
};

