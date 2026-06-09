<?php 

    class DocumentCrypt {
        private const SECRET_KEY = 'Eduardaamor15#';

        public static function encrypt(string $file): string {
            $iv = random_bytes(16);
            
            $encrypted = openssl_encrypt($file, 
            'AES-256-CBC',
            hash('sha256', self::SECRET_KEY, true),
            OPENSSL_RAW_DATA,
            $iv
            );
            return rtrim(strtr(base64_encode($iv . $encrypted), '+/', '-_'), '=');
        }

        public static function decrypt(string $token): ?string {
            $data = base64_decode(strtr($token, '-_', '+/'));
            if (!$data || strlen($data) < 17) {
                return null;
            }
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                hash('sha256', self::SECRET_KEY, true),
                OPENSSL_RAW_DATA,
                $iv
            );
            return $decrypted ?: null;
        }
    }
