import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
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
/**
* @see \App\Http\Controllers\WarehouseController::store
 * @see app/Http/Controllers/WarehouseController.php:86
 * @route '/api/warehouse'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/warehouse',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WarehouseController::store
 * @see app/Http/Controllers/WarehouseController.php:86
 * @route '/api/warehouse'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::store
 * @see app/Http/Controllers/WarehouseController.php:86
 * @route '/api/warehouse'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\WarehouseController::store
 * @see app/Http/Controllers/WarehouseController.php:86
 * @route '/api/warehouse'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WarehouseController::store
 * @see app/Http/Controllers/WarehouseController.php:86
 * @route '/api/warehouse'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:153
 * @route '/api/warehouse/{warehouse}'
 */
export const update = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/warehouse/{warehouse}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:153
 * @route '/api/warehouse/{warehouse}'
 */
update.url = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { warehouse: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { warehouse: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    warehouse: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        warehouse: typeof args.warehouse === 'object'
                ? args.warehouse.id
                : args.warehouse,
                }

    return update.definition.url
            .replace('{warehouse}', parsedArgs.warehouse.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:153
 * @route '/api/warehouse/{warehouse}'
 */
update.put = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:153
 * @route '/api/warehouse/{warehouse}'
 */
    const updateForm = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:153
 * @route '/api/warehouse/{warehouse}'
 */
        updateForm.put = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\WarehouseController::destroy
 * @see app/Http/Controllers/WarehouseController.php:205
 * @route '/api/warehouse/{warehouse}'
 */
export const destroy = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/warehouse/{warehouse}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\WarehouseController::destroy
 * @see app/Http/Controllers/WarehouseController.php:205
 * @route '/api/warehouse/{warehouse}'
 */
destroy.url = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { warehouse: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { warehouse: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    warehouse: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        warehouse: typeof args.warehouse === 'object'
                ? args.warehouse.id
                : args.warehouse,
                }

    return destroy.definition.url
            .replace('{warehouse}', parsedArgs.warehouse.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::destroy
 * @see app/Http/Controllers/WarehouseController.php:205
 * @route '/api/warehouse/{warehouse}'
 */
destroy.delete = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\WarehouseController::destroy
 * @see app/Http/Controllers/WarehouseController.php:205
 * @route '/api/warehouse/{warehouse}'
 */
    const destroyForm = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WarehouseController::destroy
 * @see app/Http/Controllers/WarehouseController.php:205
 * @route '/api/warehouse/{warehouse}'
 */
        destroyForm.delete = (args: { warehouse: number | { id: number } } | [warehouse: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const WarehouseController = { index, list, store, update, destroy }

export default WarehouseController