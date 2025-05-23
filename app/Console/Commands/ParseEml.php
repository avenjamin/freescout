<?php

namespace App\Console\Commands;

use App\Mailbox;
use Webklex\PHPIMAP\IMAP;
use Webklex\PHPIMAP\Message;
use Illuminate\Console\Command;


class ParseEml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freescout:parse-eml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse EML file';

    /**
     * Current mailbox.
     *
     * Used to process emails sent to multiple mailboxes.
     */
    public $mailbox;

    /**
     * Used to process emails sent to multiple mailboxes.
     */
    public $mailboxes;

    public $extra_import = [];

    /**
     * Page size when requesting emails from mail server.
     */
    const PAGE_SIZE = 300;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = file_get_contents(storage_path('logs/email.eml'));

        if (!str_contains($email, "\r\n")){
            $email = str_replace("\n", "\r\n", $email);
        }

        // $raw_header = substr($email, 0, strpos($email, "\r\n\r\n"));
        // $raw_body = substr($email, strlen($raw_header)+4);

        // $mailbox = Mailbox::find($this->option('mailbox'));

        // //\Config::set('app.new_fetching_library', 'true');
        // $client = \MailHelper::getMailboxClient($mailbox);
        // $client->openFolder("INBOX");

        // $message = Message::make(/*$this->option('uid')*/null, null, $client, $raw_header, $raw_body, [/*0 => "\\Seen"*/], IMAP::ST_UID);

        $manager = new \Webklex\PHPIMAP\ClientManager([
            // 'options' => [
            //     "debug" => $_ENV["LIVE_MAILBOX_DEBUG"] ?? false,
            // ],
            // 'accounts' => [
            //     'default' => [
            //         'host'          => getenv("LIVE_MAILBOX_HOST"),
            //         'port'          => getenv("LIVE_MAILBOX_PORT"),
            //         'encryption'    => getenv("LIVE_MAILBOX_ENCRYPTION"),
            //         'validate_cert' => getenv("LIVE_MAILBOX_VALIDATE_CERT"),
            //         'username'      => getenv("LIVE_MAILBOX_USERNAME"),
            //         'password'      => getenv("LIVE_MAILBOX_PASSWORD"),
            //         'protocol'      => 'imap', //might also use imap, [pop3 or nntp (untested)]
            //     ],
            // ],
        ]);
        $message = \Webklex\PHPIMAP\Message::fromString($email);

        $this->line('Headers: ');
        $this->info($message->getHeader()->raw);
        $this->line('From: ');
        $this->info(json_encode($message->getFrom()[0] ?? [], JSON_UNESCAPED_UNICODE));
        $this->line('Reply-To: ');
        $this->info(json_encode($message->getReplyTo()[0] ?? [], JSON_UNESCAPED_UNICODE));
        $this->line('In-Reply-To: ');
        $this->info($message->getInReplyTo());
        $this->line('References: ');
        $this->info(json_encode(array_values(array_filter(preg_split('/[, <>]/', $message->getReferences() ?? ''))), JSON_UNESCAPED_UNICODE));
        $this->line('Date: ');
        $this->info($message->getDate());
        $this->line('Subject: ');
        $this->info($message->getSubject());
        $this->line('Text Body: ');
        $this->info($message->getTextBody());
        $this->line('HTML Body: ');
        $html_body = $message->getHTMLBody(false) ?? '';
        $this->info($html_body);

        $attachments = $message->getAttachments();
        if (count($attachments)) {
            $this->line('Attachments: ');
            foreach ($attachments as $attachment) {
                $this->info('— '.$attachment->getName().(strstr($html_body, 'cid:'.$attachment->id) ? ' (embedded)' : ''));
            }
        }
    }
}
