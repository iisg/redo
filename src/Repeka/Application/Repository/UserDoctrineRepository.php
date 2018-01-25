<?php
namespace Repeka\Application\Repository;

use Assert\Assertion;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserDoctrineRepository extends AbstractRepository implements UserRepository, UserLoaderInterface {
    public function loadUserByUsername($username) {
        $usernameMetadataId = SystemMetadata::USERNAME;

        $resultSetMapping = ResultSetMappings::user($this->getEntityManager());
        $query = $this->getEntityManager()->createNativeQuery(<<<SQL
            SELECT "user".* FROM "user"
              INNER JOIN "resource" user_data ON "user".user_data_id = user_data.id
              WHERE (contents->'$usernameMetadataId'->0->'value')::jsonb @> :username
SQL
            , $resultSetMapping);
        // the value needs to be double quoted for @> operator
        // see: https://stackoverflow.com/a/38328942/878514
        $query->setParameter('username', '"' . $username . '"');
        return $query->getOneOrNullResult();
    }

    public function save(User $user): User {
        return $this->persist($user);
    }

    public function findOne(int $id): User {
        return $this->findById($id);
    }

    public function findByUserData(ResourceEntity $resource): User {
        Assertion::eq($resource->getKind()->getId(), SystemResourceKind::USER);
        /** @var User $user */
        $user = $this->findOneBy(['userData' => $resource]);
        return $user;
    }
}
