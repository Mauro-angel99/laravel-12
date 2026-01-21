import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/work-parameters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkParameterController::index
 * @see app/Http/Controllers/WorkParameterController.php:13
 * @route '/api/work-parameters'
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
* @see \App\Http\Controllers\WorkParameterController::store
 * @see app/Http/Controllers/WorkParameterController.php:22
 * @route '/api/work-parameters'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/work-parameters',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkParameterController::store
 * @see app/Http/Controllers/WorkParameterController.php:22
 * @route '/api/work-parameters'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkParameterController::store
 * @see app/Http/Controllers/WorkParameterController.php:22
 * @route '/api/work-parameters'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\WorkParameterController::store
 * @see app/Http/Controllers/WorkParameterController.php:22
 * @route '/api/work-parameters'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkParameterController::store
 * @see app/Http/Controllers/WorkParameterController.php:22
 * @route '/api/work-parameters'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\WorkParameterController::update
 * @see app/Http/Controllers/WorkParameterController.php:48
 * @route '/api/work-parameters/{workParameter}'
 */
export const update = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/work-parameters/{workParameter}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\WorkParameterController::update
 * @see app/Http/Controllers/WorkParameterController.php:48
 * @route '/api/work-parameters/{workParameter}'
 */
update.url = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { workParameter: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { workParameter: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    workParameter: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workParameter: typeof args.workParameter === 'object'
                ? args.workParameter.id
                : args.workParameter,
                }

    return update.definition.url
            .replace('{workParameter}', parsedArgs.workParameter.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkParameterController::update
 * @see app/Http/Controllers/WorkParameterController.php:48
 * @route '/api/work-parameters/{workParameter}'
 */
update.put = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\WorkParameterController::update
 * @see app/Http/Controllers/WorkParameterController.php:48
 * @route '/api/work-parameters/{workParameter}'
 */
    const updateForm = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkParameterController::update
 * @see app/Http/Controllers/WorkParameterController.php:48
 * @route '/api/work-parameters/{workParameter}'
 */
        updateForm.put = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\WorkParameterController::destroy
 * @see app/Http/Controllers/WorkParameterController.php:66
 * @route '/api/work-parameters/{workParameter}'
 */
export const destroy = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/work-parameters/{workParameter}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\WorkParameterController::destroy
 * @see app/Http/Controllers/WorkParameterController.php:66
 * @route '/api/work-parameters/{workParameter}'
 */
destroy.url = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { workParameter: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { workParameter: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    workParameter: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workParameter: typeof args.workParameter === 'object'
                ? args.workParameter.id
                : args.workParameter,
                }

    return destroy.definition.url
            .replace('{workParameter}', parsedArgs.workParameter.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkParameterController::destroy
 * @see app/Http/Controllers/WorkParameterController.php:66
 * @route '/api/work-parameters/{workParameter}'
 */
destroy.delete = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\WorkParameterController::destroy
 * @see app/Http/Controllers/WorkParameterController.php:66
 * @route '/api/work-parameters/{workParameter}'
 */
    const destroyForm = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkParameterController::destroy
 * @see app/Http/Controllers/WorkParameterController.php:66
 * @route '/api/work-parameters/{workParameter}'
 */
        destroyForm.delete = (args: { workParameter: number | { id: number } } | [workParameter: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const workParameters = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default workParameters