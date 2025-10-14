<?php

namespace App\Domain\Users\UseCases;

use App\Domain\Users\Entities\User;
use App\Domain\Users\ValueObjects\Phone;
use App\Domain\Users\ValueObjects\Type;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\EnterpriseResource;
use App\Http\Resources\PartnerResource;
use App\Infrastructure\Persistence\CustomerRepository;
use App\Infrastructure\Persistence\EnterpriseRepository;
use App\Infrastructure\Persistence\PartnerRepository;
use App\Infrastructure\Persistence\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User as ModelUser;
use function Laravel\Prompts\error;

class RegisterUser
{

    public function __construct(private UserRepository $userRepository, private CustomerRepository $customerRepository, private PartnerRepository $partnerRepository, private EnterpriseRepository $enterpriseRepository){}

    public function execute(array $dto)
    {
        DB::beginTransaction();
        try {
            //Instance of the entity user
            $user = new User(
                id: Str::uuid()->toString(),
                type: $dto['type'],
                firstName: !isset($dto['first_name']) ? null : $dto['first_name'],
                lastName: !isset($dto['last_name']) ? null : $dto['last_name'],
                gender: !isset($dto['gender']) ? null : $dto['gender'],
                phone: new Phone($dto['phone']),
                whatsApp: new Phone($dto['whatsApp']),
                email: $dto['email'],
                passwordHash: Hash::make($dto['password']),
                name: !isset($dto['name']) ? null : $dto['name'],
                customerId: Str::uuid()->toString(),
                partnerId: Str::uuid()->toString(),
                enterpriseId: Str::uuid()->toString()
            );

            //Create the model user
            $modelUser = $this->userRepository->save($user);
            /** Now create the child model between('customer', 'partner', 'enterprise')*/
            match ($dto['type']) {
                Type::Customer->value => $this->customerRepository->save($user),
                Type::Enterprise->value => $this->enterpriseRepository->save($user),
                Type::Partner->value => $this->partnerRepository->save($user),
            };

            DB::commit();

            return $modelUser;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log:error($e->getMessage());
            return (Object)$e->getMessage();
        }
    }
}
