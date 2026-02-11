import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/work-phase-images',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseImageController::index
 * @see app/Http/Controllers/WorkPhaseImageController.php:19
 * @route '/api/work-phase-images'
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
* @see \App\Http\Controllers\WorkPhaseImageController::store
 * @see app/Http/Controllers/WorkPhaseImageController.php:38
 * @route '/api/work-phase-images'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/work-phase-images',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkPhaseImageController::store
 * @see app/Http/Controllers/WorkPhaseImageController.php:38
 * @route '/api/work-phase-images'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseImageController::store
 * @see app/Http/Controllers/WorkPhaseImageController.php:38
 * @route '/api/work-phase-images'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\WorkPhaseImageController::store
 * @see app/Http/Controllers/WorkPhaseImageController.php:38
 * @route '/api/work-phase-images'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseImageController::store
 * @see app/Http/Controllers/WorkPhaseImageController.php:38
 * @route '/api/work-phase-images'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\WorkPhaseImageController::destroy
 * @see app/Http/Controllers/WorkPhaseImageController.php:108
 * @route '/api/work-phase-images/{workPhaseImage}'
 */
export const destroy = (args: { workPhaseImage: number | { id: number } } | [workPhaseImage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/work-phase-images/{workPhaseImage}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\WorkPhaseImageController::destroy
 * @see app/Http/Controllers/WorkPhaseImageController.php:108
 * @route '/api/work-phase-images/{workPhaseImage}'
 */
destroy.url = (args: { workPhaseImage: number | { id: number } } | [workPhaseImage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { workPhaseImage: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { workPhaseImage: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    workPhaseImage: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        workPhaseImage: typeof args.workPhaseImage === 'object'
                ? args.workPhaseImage.id
                : args.workPhaseImage,
                }

    return destroy.definition.url
            .replace('{workPhaseImage}', parsedArgs.workPhaseImage.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseImageController::destroy
 * @see app/Http/Controllers/WorkPhaseImageController.php:108
 * @route '/api/work-phase-images/{workPhaseImage}'
 */
destroy.delete = (args: { workPhaseImage: number | { id: number } } | [workPhaseImage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\WorkPhaseImageController::destroy
 * @see app/Http/Controllers/WorkPhaseImageController.php:108
 * @route '/api/work-phase-images/{workPhaseImage}'
 */
    const destroyForm = (args: { workPhaseImage: number | { id: number } } | [workPhaseImage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseImageController::destroy
 * @see app/Http/Controllers/WorkPhaseImageController.php:108
 * @route '/api/work-phase-images/{workPhaseImage}'
 */
        destroyForm.delete = (args: { workPhaseImage: number | { id: number } } | [workPhaseImage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const WorkPhaseImageController = { index, store, destroy }

export default WorkPhaseImageController