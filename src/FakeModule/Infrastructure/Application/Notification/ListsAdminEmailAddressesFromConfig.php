<?php
namespace Repeka\FakeModule\Infrastructure\Application\Notification;

use Repeka\FakeModule\Application\Notification\ListsAdminEmailAddresses;
use Repeka\CoreModule\Domain\EmailAddress;
use Repeka\CoreModule\Domain\EmailAddressList;

class ListsAdminEmailAddressesFromConfig implements ListsAdminEmailAddresses {
    /**
     * @var EmailAddressList
     */
    private $emailAddressList;

    /**
     * ListAdminEmailAddressFromConfig constructor.
     * @param array $listAdminEmailAddress
     */
    public function __construct(array $listAdminEmailAddress) {
        $this->emailAddressList = new EmailAddressList();
        foreach ($listAdminEmailAddress as $emailAddress) {
            $this->emailAddressList->add(new EmailAddress($emailAddress));
        }
    }

    public function getAdminEmailAddresses() : EmailAddressList {
        return $this->emailAddressList;
    }
}
