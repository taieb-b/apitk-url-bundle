# RFC14 - Filter, Sorting and Pagination for RESTful API's   

## Installation
Add this repository to your `composer.json` until it is available at packagist:
```
{
    "repositories": [{
            "type": "vcs",
            "url": "git@github.com:ofeige/rfc14-bundle.git"
        }
    ]
}
```

After that, install the package via composer:
```
composer install ofeige/rfc14-bundle:dev-master
```

## Usage
### Filtering
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
     * @Rfc14\Filter(name="country", queryBuilderName="a.country")
     *
     * @return User[]
     */
```
Limit the possibilities for the user with the `allowedComparisons` option.

If you want to use the built in query builder applier and the entity field name differs from the filter field name (f.e. because it's a field from a joined tabled with an alias) use the `queryBuilderName` option.

The client can now call your API endpoint with filter options like `GET /v1/users?filter[created][gt]=2018-01-01&filter[country][in]=DE,AT`. If the client specified invalid filters (fields or comparisons you didn't configure/allow), the client will get a 400 response.

### Sorting
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

### Pagination
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
