# RFC14 - Filter, Sorting and Pagination for RESTful API's   

## Installation
Add this repository to your `composer.json` until it is available at packagist:
```
{
    "repositories": [{
            "type": "vcs",
            "url": "git@github.com:CHECK24/rfc14-bundle.git"
        }
    ]
}
```

After that, install the package via composer:
```
composer install ofeige/rfc14-bundle:dev-master
```

## Usage
### Defining possible filers, sortings and pagination
#### Filtering
You can specify in the annotations of the action, which fields should be filterable by the client:
```
use Ofeige\Rfc14Bundle\Annotation as Rfc14;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @Rfc14\Filter(name="username")
 * @Rfc14\Filter(name="created", allowedComparisons={"gt","lt"})
 * @Rfc14\Filter(name="active", enum={"true","false"})
 * @Rfc14\Filter(name="country", queryBuilderName="a.country")
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
 * @Rfc14\Filter(name="id", queryBuilderName="u.id")
 *
 * @return Address[]
 */
```

#### Sorting
You can specify in the annotations of the action, which fields should be sortable by the client:
```
use Ofeige\Rfc14Bundle\Annotation as Rfc14;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @Rfc14\Sort(name="username")
 * @Rfc14\Sort(name="zipcode", queryBuilderName="a.zipCode", allowedDirections={"asc"})
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
use Ofeige\Rfc14Bundle\Annotation as Rfc14;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v1/users")
 * @Rest\View()
 *
 * @Rfc14\Pagination
 * Or:
 * @Rfc14\Pagination(maxEntries=25)
 *
 * @return User[]
 */
```
If you want to limit the client, how many items per page he gets, you can specify the `maxEntries` option. But please only add one `Pagination` annotation (not like in the example above).

The client can now call your API endpoint with the limit option like `GET /v1/users?limit=10` (get the first 10 entries) or `GET /v1/users?limit=30,10` (get the 10 entries with an offset of 30 (=4th page)). If the client tries to paginate when it isn't enabled by you or he wants to get more items per page than specified by you, the client will get a 400 response.

Also a new header `x-rfc14-pagination-total` is sent in the response containing the total amount of entries, so the client can adjust his pagination buttons.

### Accessing client input
#### Autoloading array through param converter
If you have a default case, just implement Rfc14RepositoryInterface to your repository. You can do this automatically so you even don't have to create a repository for your entity. Just add this line to your `doctrine.yaml`:
```
doctrine:
    orm:
        default_repository_class: Ofeige\Rfc14Bundle\Repository\Rfc14Repository
```
After that, add a `@Rfc14\Result` annotation to your controller action. The action parameter will automatically gets filled with the filtered, sorted and paginated result set of the given entity's repository:
```
//ItemController.php
/**
 * @Rfc14\Filter(name="name")
 * @Rfc14\Sort(name="name")
 * @Rfc14\Pagination
 *
 * @Rfc14\Result("items", entity="App\Entity\Item")
 */
public function getItems(array $items)
{
    return $items;
}
```
If you need to filter/sort for fields in a joined entity, just define your own `findByRfc14()` method in the custom entity's repository:
```
//UserRepository.php
use Ofeige\Rfc14Bundle\Repository\Rfc14Repository;
class UserRepository extends Rfc14Repository
{
    public function findByRfc14(Rfc14Service $rfc14Service): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.addresses', 'a')->distinct();

        $rfc14Service->applyToQueryBuilder($qb);

        return $qb->getQuery()->getResult();
    }
}
```
```
//UserController.php
/**
 * @Rfc14\Filter(name="username")
 * @Rfc14\Filter(name="country", queryBuilderName="a.country")
 * @Rfc14\Pagination
 *
 * @Rfc14\Result("users", entity="App\Entity\User")
 */
public function getUsers(array $users)
{
    return $users;
}
```
Note: if the paginator was enabled in your annotations, the query will get executed by the `applyToQueryBuilder()` method in your repository to determine the total count. Be sure to call the method at the end, after building the rest of your query.

#### Manually accessing
If you have to implement custom logic with filtering, sorting and pagination, you can also inject the `Rfc14Service` and use its methods:
```
//UserController.php
public function getUsersV1(EntityManagerInterface $entityManager, Rfc14Service $rfc14Service)
{
    $users = $entityManager->getRepository(User::class)->findAll();

    //Filtering
    if ($rfc14Service->hasFilteredField('username')) {
        $usernameFilter = $rfc14Service->getFilteredField('username');
        $users = array_filter($users, function($user) use ($usernameFilter) { 
            if ($usernameFilter->getComparison() === Rfc14\Filter::COMPARISON_EQUALS) {
                return $user->getUsername() === $usernameFilter->getValue();
            }
            return false;
        });
    }
    /*...*/
    
    //Sorting
    foreach (array_reverse($rfc14Service->getSortedFields()) as $sortField) {
        if ($sortField->getName() === 'username') {
            usort($users, function($user1, $user2) use ($sortField) {
                if ($sortField->getDirection === Rfc14\Sort::ASCENDING) {
                    return $user1->getUsername() <=> $user2->getUsername();
                } else {
                    return $user2->getUsername() <=> $user1->getUsername();
                }
            });
        }
        /*...*/
    }
    
    //Pagination
    $rfc14Service->setPaginationTotal(count($users));
    $users = array_slice($users, $rfc14Service->getPaginationOffset(), $rfc14Service->getPaginationLimit());

    return $users;
}

```
### Documentation
The defined filers, sorts and pagination will automatically get added to the NelmioApiDoc output (aka Swagger UI). You don't have to worry about that.
