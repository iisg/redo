<?php
namespace Repeka\FakeModule\Application\Notification;

use Repeka\CoreModule\Domain\EmailAddressList;

interface ListsAdminEmailAddresses {
    public function getAdminEmailAddresses() : EmailAddressList;
}
