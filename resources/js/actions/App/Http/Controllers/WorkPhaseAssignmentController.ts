import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/assigned-work-phases',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:14
 * @route '/assigned-work-phases'
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
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
 */
export const list = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/api/assigned-work-phases',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:20
 * @route '/api/assigned-work-phases'
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
const WorkPhaseAssignmentController = { index, list }

export default WorkPhaseAssignmentController