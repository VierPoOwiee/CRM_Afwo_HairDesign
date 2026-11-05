<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMail extends Command
{
    protected $signature = 'mail:test {email : Alamat email tujuan}';

    protected $description = 'Kirim email uji secara sinkron untuk memverifikasi konfigurasi email/SMTP.';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Alamat email tidak valid.');

            return self::FAILURE;
        }

        $mailer = (string) config('mail.default');
        $mailerConfig = (array) config("mail.mailers.{$mailer}", []);

        $this->line('Mailer aktif : '.$mailer);
        $this->line('Host        : '.($mailerConfig['host'] ?? '-'));
        $this->line('Port        : '.($mailerConfig['port'] ?? '-'));
        $this->line('From        : '.(config('mail.from.address') ?: '-'));

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("Mailer {$mailer} tidak mengirim email ke kotak masuk sungguhan.");
        }

        try {
            Mail::raw(
                'Ini adalah email uji dari AFWO Hair Design. Jika Anda menerima email ini, konfigurasi pengiriman email berhasil.',
                function ($message) use ($email) {
                    $message->to($email)->subject('Uji Pengiriman Email — AFWO Hair Design');
                }
            );
        } catch (\Throwable $e) {
            $this->error('Gagal mengirim email: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Email uji berhasil dikirim ke {$email}.");

        return self::SUCCESS;
    }
}
