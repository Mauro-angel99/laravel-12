import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/work-phases',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseController::index
 * @see app/Http/Controllers/WorkPhaseController.php:11
 * @route '/work-phases'
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
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
 */
export const list = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/api/work-phases',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseController::list
 * @see app/Http/Controllers/WorkPhaseController.php:17
 * @route '/api/work-phases'
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
* @see \App\Http\Controllers\WorkPhaseController::confirm
 * @see app/Http/Controllers/WorkPhaseController.php:118
 * @route '/api/work-phases/confirm'
 */
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(options),
    method: 'post',
})

confirm.definition = {
    methods: ["post"],
    url: '/api/work-phases/confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkPhaseController::confirm
 * @see app/Http/Controllers/WorkPhaseController.php:118
 * @route '/api/work-phases/confirm'
 */
confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseController::confirm
 * @see app/Http/Controllers/WorkPhaseController.php:118
 * @route '/api/work-phases/confirm'
 */
confirm.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\WorkPhaseController::confirm
 * @see app/Http/Controllers/WorkPhaseController.php:118
 * @route '/api/work-phases/confirm'
 */
    const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirm.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseController::confirm
 * @see app/Http/Controllers/WorkPhaseController.php:118
 * @route '/api/work-phases/confirm'
 */
        confirmForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirm.url(options),
            method: 'post',
        })
    
    confirm.form = confirmForm
const WorkPhaseController = { index, list, confirm }

export default WorkPhaseController