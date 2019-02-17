# apitk-url-bundle: Filter, Sorting and Pagination for RESTful API's   

## Installation
Install the package via composer:
```
composer require check24/apitk-url-bundle
```

## Usage
### Defining possible filers, sortings and pagination
#### Filtering
You can specify in the annotations of the action, which fields should be filterable by the client:
```
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @ApiTK\Filter(name="username")
 * @ApiTK\Filter(name="created", allowedComparisons={"gt","lt"})
 * @ApiTK\Filter(name="active", enum={"true","false"})
 * @ApiTK\Filter(name="country", queryBuilderName="a.country")
 *
 * @return User[]
 */
```
Limit the possibilities for the user with the `allowedComparisons` and `enum` option.

If you want to use the built in query builder applier and the entity field name differs from the 
filter field name (f.e. because it's a field from a joined tabled with an alias) use the
`queryBuilderName` option.

The client can now call your API endpoint with filter options like 
`GET /v1/users?filter[created][gt]=2018-01-01&filter[country][in]=DE,AT`. If the client specified 
invalid filters (fields or comparisons you didn't configure/allow), the client will get a 400 
response.

You can also use route parameters for filtering input. Just declare the filter with the same 
name the placeholder in the route is named:
```
/**
 * Returns the addresses for the given user.
 *
 * @Rest\Get("/v1/users/{id}/addresses")
 * @Rest\View()
 *
 * @ApiTK\Filter(name="id", queryBuilderName="u.id")
 *
 * @return Address[]
 */
```

#### Sorting
You can specify in the annotations of the action, which fields should be sortable by the client:
```
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @ApiTK\Sort(name="username")
 * @ApiTK\Sort(name="zipcode", queryBuilderName="a.zipCode", allowedDirections={"asc"})
 *
 * @return User[]
 */
```
Limit the possibilities for the user with the `allowedDirections` option (f.e. if you only want to 
support ascending sorting).

If you want to use the built in query builder applier and the entity field name differs from the 
sort field name (f.e. because it's a field from a joined tabled with an alias) use the 
`queryBuilderName` option.

The client can now call your API endpoint with sort options like 
`GET /v1/users?sort[zipcode]=asc&sort[username]=desc`. If the client specified invalid sorts 
(fields or directions you didn't configure/allow), the client will get a 400 response.

#### Pagination
You can specify in the annotations of the action, if the result should be paginatable by the client:
```
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @ApiTK\Pagination
 * Or:
 * @ApiTK\Pagination(maxEntries=25)
 *
 * @return User[]
 */
```
If you want to limit the client, how many items per page he gets, you can specify the 
`maxEntries` option. But please only add one `Pagination` annotation (not like in the example 
above).

The client can now call your API endpoint with the limit option like 
`GET /v1/users?limit=10` (get the first 10 entries) or `GET /v1/users?limit=30,10` (get the 
10 entries with an offset of 30 (=4th page)). If the client tries to paginate when it isn't 
enabled by you or he wants to get more items per page than specified by you, the client will 
get a 400 response.

Also a new header `x-apitk-pagination-total` is sent in the response containing the total 
amount of entries, so the client can adjust his pagination buttons.

### Accessing client input
#### Autoloading array through param converter
You can automatically get the entity result array of all your filters in the correct order and pagination subset easily injected into your action.

First set your default repository in your `doctrine.yaml` to our `ApiToolkitRepository`:
```
doctrine:
    orm:
        default_repository_class: Shopping\ApiTKUrlBundle\Repository\ApiToolkitRepository
```
For all your custom repositories, extend them from the `ApiToolkitRepository` so they also get the needed functionality.

After that, add a `@ApiTK\Result` annotation to your controller action. The action parameter 
will automatically gets filled with the filtered, sorted and paginated result set of the 
given entity's repository:
```
//ItemController.php
/**
 * @ApiTK\Filter(name="name")
 * @ApiTK\Sort(name="name")
 * @ApiTK\Pagination
 *
 * @ApiTK\Result("items", entity="App\Entity\Item")
 */
public function getItems(array $items)
{
    return $items;
}
```

If you need to filter/sort for fields in a joined entity, just define your own 
`findByRequest()` method in the custom entity's repository:
```
//UserRepository.php
use Shopping\ApiTKUrlBundle\Repository\ApiToolkitRepository;
class UserRepository extends ApiToolkitRepository
{
    public function findByRequest(ApiService $apiService): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.addresses', 'a')->distinct();

        $apiService->applyToQueryBuilder($qb);

        return $qb->getQuery()->getResult();
    }
}
```
```
//UserController.php
/**
 * @ApiTK\Filter(name="username")
 * @ApiTK\Filter(name="country", queryBuilderName="a.country")
 * @ApiTK\Pagination
 *
 * @ApiTK\Result("users", entity="App\Entity\User")
 */
public function getUsers(array $users)
{
    return $users;
}
```
If you need to add different methods to your repository, that can be executed by the 
`Result` annotation, than you can add the `methodName="findBySomethingElse"` parameter 
to your annotation. It will then look for this method in your repository instead of 
the default `findByResult()` method. Be sure to accept as the sole parameter the `ApiService`.

As a result, this is also possible:
```
//UserRepository.php
use Shopping\ApiTKUrlBundle\Repository\ApiToolkitRepository;
class UserRepository extends ApiToolkitRepository
{
    public function findBarBaz(ApiService $apiService): array
    {
        // your own logic
    }
}
```
```
//UserController.php
/**
 * @ApiTK\Result("users", entity="App\Entity\User", methodName="findBarBaz")
 */
public function getUsers(array $users)
{
    return $users;
}
```

Note: if the paginator was enabled in your annotations, the query will get 
executed by the `applyToQueryBuilder()` method in your repository to determine the 
total count. Be sure to call the method at the end, after building the rest of your query.

You can specify the entity manager by adding a `entityManager="foobar"` to your 
annotation, if you need to use another entity manager / connection than the default one.

#### Manually accessing
If you have to implement custom logic with filtering, sorting and pagination, 
you can also inject the `ApiService` and use its methods:
```
//UserController.php
public function getUsersV1(EntityManagerInterface $entityManager, ApiService $apiService)
{
    $users = $entityManager->getRepository(User::class)->findAll();

    //Filtering
    if ($apiService->hasFilteredField('username')) {
        $usernameFilter = $apiService->getFilteredField('username');
        $users = array_filter($users, function($user) use ($usernameFilter) { 
            if ($usernameFilter->getComparison() === ApiTK\Filter::COMPARISON_EQUALS) {
                return $user->getUsername() === $usernameFilter->getValue();
            }
            return false;
        });
    }
    /*...*/
    
    //Sorting
    foreach (array_reverse($apiService->getSortedFields()) as $sortField) {
        if ($sortField->getName() === 'username') {
            usort($users, function($user1, $user2) use ($sortField) {
                if ($sortField->getDirection === ApiTK\Sort::ASCENDING) {
                    return $user1->getUsername() <=> $user2->getUsername();
                } else {
                    return $user2->getUsername() <=> $user1->getUsername();
                }
            });
        }
        /*...*/
    }
    
    //Pagination
    $apiService->setPaginationTotal(count($users));
    $users = array_slice($users, $apiService->getPaginationOffset(), $apiService->getPaginationLimit());

    return $users;
}

```

#### Implementing only some filters manually
In case you have some "virtual" fields that need some custom logic, you can use applyToQueryBuilder and  
 still define your field by hand.

Let's say you want to implement a search parameter. This search looks into username and email.
```
//UserController.php
/**
 * @ApiTK\Filter(name="search", autoApply=false)
 *
 * @ApiTK\Result("users", entity="App\Entity\User")
 */
public function getUsers(array $users)
{
    return $users;
}
```

For your parameter to be available, you need to register a new `Filter` field. It is required to set 
`autoApply=false` for this filter, because there's no "search" field on the Entity and we want to assemble this
part of the query on our own.

```
//UserRepository.php
use Shopping\ApiTKUrlBundle\Repository\ApiToolkitRepository;
class UserRepository extends ApiToolkitRepository
{
    public function findByRequest(ApiService $apiService): array
    {
        $qb = $this->createQueryBuilder('u');
        
        if ($apiService->hasFilteredField('search')) {
            $search = $apiService->getFilteredField('search');
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.username', ':query'),
                    $qb->expr()->like('u.email', ':query')
                )
            )->setParameter('query', '%' . $search . '%');
        }

        $apiService->applyToQueryBuilder($qb);

        return $qb->getQuery()->getResult();
    }
}
```

`applyToQueryBuilder` will skip our `autoApply=false` field, so we can add it ourselves.

Important: When using also the paginator, call the `$apiService->applyToQueryBuilder()` method AFTER your manual filtering, so the paginator can build the total count header on the filtered result. Otherwise you get incorrect values in your header.

#### Implementing a sort manually
Same as with manual filter properties, you can implement manual sorting like shown above:

```
//UserController.php
/**
 * @ApiTK\Sort(name="mySortProperty", autoApply=false)
 *
 * @ApiTK\Result("users", entity="App\Entity\User")
 */
public function getUsers(array $users)
{
    return $users;
}
```

```
//UserRepository.php
use Shopping\ApiTKUrlBundle\Repository\ApiToolkitRepository;
class UserRepository extends ApiToolkitRepository
{
    public function findByRequest(ApiService $apiService): array
    {
        $qb = $this->createQueryBuilder('u');

        $apiService->applyToQueryBuilder($qb);
    
        if ($apiService->hasSortedField('mySortProperty')) {
            // perform your own sorting logic for this field
        }

        return $qb->getQuery()->getResult();
    }
}

```

For more advanced purposes, see refer to the "Manually accessing" section above.


### Documentation
The defined filers, sorts and pagination will automatically get added to the 
NelmioApiDoc output (aka Swagger UI). You don't have to worry about that.
