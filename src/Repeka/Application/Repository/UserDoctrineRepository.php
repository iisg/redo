<?php
namespace Repeka\Application\Repository;

use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserDoctrineRepository extends AbstractRepository implements UserRepository, UserLoaderInterface {
    public function loadUserByUsername($username) {
        $usernameMetadataId = SystemMetadata::USERNAME;
        $resultSetMapping = ResultSetMappings::user($this->getEntityManager());
        $userKindId = SystemResourceKind::USER;
        $query = $this->getEntityManager()->createNativeQuery(
            <<<SQL
            SELECT "user".* FROM "user"
              INNER JOIN "resource" user_data ON "user".user_data_id = user_data.id
              WHERE kind_id = $userKindId AND (contents->'$usernameMetadataId'->0->'value')::jsonb @> LOWER(:username)::JSONB
SQL
            ,
            $resultSetMapping
        );
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
        /** @var User $user */
        $user = $this->findOneBy(['userData' => $resource]);
        if (!$user) {
            throw new EntityNotFoundException($this, "userData#{$resource->getId()}");
        }
        return $user;
    }
}
