import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
export const get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: get.url(options),
    method: 'get',
})

get.definition = {
    methods: ["get","head"],
    url: '/api/job-parameter-values',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
get.url = (options?: RouteQueryOptions) => {
    return get.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
get.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: get.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
get.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: get.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
    const getForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: get.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
        getForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: get.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::get
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
        getForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: get.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    get.form = getForm
const jobParameterValues = {
    get: Object.assign(get, get),
}

export default jobParameterValues