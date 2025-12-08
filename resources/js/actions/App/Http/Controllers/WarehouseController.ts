import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/warehouse',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WarehouseController::index
 * @see app/Http/Controllers/WarehouseController.php:14
 * @route '/warehouse'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
export const list = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/api/warehouse',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WarehouseController::list
 * @see app/Http/Controllers/WarehouseController.php:20
 * @route '/api/warehouse'
 */
        listForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    list.form = listForm
const WarehouseController = { index, list }

export default WarehouseController