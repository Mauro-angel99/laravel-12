import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
export const products = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(args, options),
    method: 'get',
})

products.definition = {
    methods: ["get","head"],
    url: '/api/warehouse/positions/{position}/products',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
products.url = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { position: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    position: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        position: args.position,
                }

    return products.definition.url
            .replace('{position}', parsedArgs.position.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
products.get = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
products.head = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: products.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
    const productsForm = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: products.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
        productsForm.get = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: products.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WarehouseController::products
 * @see app/Http/Controllers/WarehouseController.php:81
 * @route '/api/warehouse/positions/{position}/products'
 */
        productsForm.head = (args: { position: string | number } | [position: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: products.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    products.form = productsForm
/**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:284
 * @route '/api/warehouse/positions/{position}'
 */
export const update = (args: { position: number | { id: number } } | [position: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/warehouse/positions/{position}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:284
 * @route '/api/warehouse/positions/{position}'
 */
update.url = (args: { position: number | { id: number } } | [position: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { position: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { position: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    position: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        position: typeof args.position === 'object'
                ? args.position.id
                : args.position,
                }

    return update.definition.url
            .replace('{position}', parsedArgs.position.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:284
 * @route '/api/warehouse/positions/{position}'
 */
update.put = (args: { position: number | { id: number } } | [position: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\WarehouseController::update
 * @see app/Http/Controllers/WarehouseController.php:284
 * @route '/api/warehouse/positions/{position}'
 */
    const updateForm = (args: { position: number | { id: number } } | [position: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/WarehouseController.php:284
 * @route '/api/warehouse/positions/{position}'
 */
        updateForm.put = (args: { position: number | { id: number } } | [position: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const position = {
    products: Object.assign(products, products),
update: Object.assign(update, update),
}

export default position