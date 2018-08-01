# api-filter-bundle: Filter, Sorting and Pagination for RESTful API's   

## Installation
Add this repository to your `composer.json` until it is available at packagist:
```
{
    "repositories": [{
            "type": "vcs",
            "url": "git@github.com:CHECK24/api-filter-bundle.git"
        }
    ]
}
```

After that, install the package via composer:
```
composer install shopping/api-filter-bundle:dev-master
```

## Usage
### Defining possible filers, sortings and pagination
#### Filtering
You can specify in the annotations of the action, which fields should be filterable by the client:
```
use Shopping\ApiFilterBundle\Annotation as Api;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @Api\Filter(name="username")
 * @Api\Filter(name="created", allowedComparisons={"gt","lt"})
 * @Api\Filter(name="active", enum={"true","false"})
 * @Api\Filter(name="country", queryBuilderName="a.country")
 *
 * @return User[]
 */
```
Limit the possibilities for the user with the `allowedComparisons` and `enum` option.

If you want to use the built in query builder applier and the entity field name differs from the filter field name (f.e. because it's a field from a joined tabled with an alias) use the `queryBuilderName` option.

The client can now call your API endpoint with filter options like `GET /v1/users?filter[created][gt]=2018-01-01&filter[country][in]=DE,AT`. If the client specified invalid filters (fields or comparisons you didn't configure/allow), the client will get a 400 response.

You can also use route parameters for filtering input. Just declare the filter with the same name the placeholder in the route is named:
```
/**
 * Returns the addresses for the given user.
 *
 * @Rest\Get("/v1/users/{id}/addresses")
 * @Rest\View()
 *
 * @Api\Filter(name="id", queryBuilderName="u.id")
 *
 * @return Address[]
 */
```

#### Sorting
You can specify in the annotations of the action, which fields should be sortable by the client:
```
use Shopping\ApiFilterBundle\Annotation as Api;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @Api\Sort(name="username")
 * @Api\Sort(name="zipcode", queryBuilderName="a.zipCode", allowedDirections={"asc"})
 *
 * @return User[]
 */
```
Limit the possibilities for the user with the `allowedDirections` option (f.e. if you only want to support ascending sorting).

If you want to use the built in query builder applier and the entity field name differs from the sort field name (f.e. because it's a field from a joined tabled with an alias) use the `queryBuilderName` option.

The client can now call your API endpoint with sort options like `GET /v1/users?sort[zipcode]=asc&sort[username]=desc`. If the client specified invalid sorts (fields or directions you didn't configure/allow), the client will get a 400 response.

#### Pagination
You can specify in the annotations of the action, if the result should be paginatable by the client:
```
use Shopping\ApiFilterBundle\Annotation as Api;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @Api\Pagination
 * Or:
 * @Api\Pagination(maxEntries=25)
 *
 * @return User[]
 */
```
If you want to limit the client, how many items per page he gets, you can specify the `maxEntries` option. But please only add one `Pagination` annotation (not like in the example above).

The client can now call your API endpoint with the limit option like `GET /v1/users?limit=10` (get the first 10 entries) or `GET /v1/users?limit=30,10` (get the 10 entries with an offset of 30 (=4th page)). If the client tries to paginate when it isn't enabled by you or he wants to get more items per page than specified by you, the client will get a 400 response.

Also a new header `x-api-pagination-total` is sent in the response containing the total amount of entries, so the client can adjust his pagination buttons.

### Accessing client input
#### Autoloading array through param converter
If you have a default case, just implement ApiRepositoryInterface to your repository. You can do this automatically so you even don't have to create a repository for your entity. Just add this line to your `doctrine.yaml`:
```
doctrine:
    orm:
        default_repository_class: Shopping\ApiFilterBundle\Repository\ApiRepository
```
After that, add a `@Api\Result` annotation to your controller action. The action parameter will automatically gets filled with the filtered, sorted and paginated result set of the given entity's repository:
```
//ItemController.php
/**
 * @Api\Filter(name="name")
 * @Api\Sort(name="name")
 * @Api\Pagination
 *
 * @Api\Result("items", entity="App\Entity\Item")
 */
public function getItems(array $items)
{
    return $items;
}
```
If you need to filter/sort for fields in a joined entity, just define your own `findByRequest()` method in the custom entity's repository:
```
//UserRepository.php
use Shopping\ApiFilterBundle\Repository\ApiRepository;
class UserRepository extends ApiRepository
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
 * @Api\Filter(name="username")
 * @Api\Filter(name="country", queryBuilderName="a.country")
 * @Api\Pagination
 *
 * @Api\Result("users", entity="App\Entity\User")
 */
public function getUsers(array $users)
{
    return $users;
}
```
If you need to add different methods to your repository, that can be executed by the `Result` annotation, than you can add the `methodName="findBySomethingElse"` parameter to your annotation. It will then look for this method in your repository instead of the default `findByResult()` method. Be sure to accept as the sole parameter the `ApiService`.

Note: if the paginator was enabled in your annotations, the query will get executed by the `applyToQueryBuilder()` method in your repository to determine the total count. Be sure to call the method at the end, after building the rest of your query.

You can specify the entity manager by adding a `entityManager="foobar"` to your annotation, if you need to use another entity manager / connection than the default one.

#### Manually accessing
If you have to implement custom logic with filtering, sorting and pagination, you can also inject the `ApiService` and use its methods:
```
//UserController.php
public function getUsersV1(EntityManagerInterface $entityManager, ApiService $apiService)
{
    $users = $entityManager->getRepository(User::class)->findAll();

    //Filtering
    if ($apiService->hasFilteredField('username')) {
        $usernameFilter = $apiService->getFilteredField('username');
        $users = array_filter($users, function($user) use ($usernameFilter) { 
            if ($usernameFilter->getComparison() === Api\Filter::COMPARISON_EQUALS) {
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
                if ($sortField->getDirection === Api\Sort::ASCENDING) {
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
### Documentation
The defined filers, sorts and pagination will automatically get added to the NelmioApiDoc output (aka Swagger UI). You don't have to worry about that.
