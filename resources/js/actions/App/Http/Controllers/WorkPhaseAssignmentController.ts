import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
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
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
 * @route '/assigned-work-phases'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
 * @route '/assigned-work-phases'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
 * @route '/assigned-work-phases'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
 * @route '/assigned-work-phases'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
 * @route '/assigned-work-phases'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::index
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:16
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
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
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
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
 * @route '/api/assigned-work-phases'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
 * @route '/api/assigned-work-phases'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
 * @route '/api/assigned-work-phases'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
 * @route '/api/assigned-work-phases'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
 * @route '/api/assigned-work-phases'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::list
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:22
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
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::updateParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:188
 * @route '/api/work-phase-assignments/{id}/parameters'
 */
export const updateParameters = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateParameters.url(args, options),
    method: 'put',
})

updateParameters.definition = {
    methods: ["put"],
    url: '/api/work-phase-assignments/{id}/parameters',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::updateParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:188
 * @route '/api/work-phase-assignments/{id}/parameters'
 */
updateParameters.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return updateParameters.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::updateParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:188
 * @route '/api/work-phase-assignments/{id}/parameters'
 */
updateParameters.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateParameters.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::updateParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:188
 * @route '/api/work-phase-assignments/{id}/parameters'
 */
    const updateParametersForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateParameters.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::updateParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:188
 * @route '/api/work-phase-assignments/{id}/parameters'
 */
        updateParametersForm.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateParameters.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateParameters.form = updateParametersForm
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
export const getParameters = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getParameters.url(options),
    method: 'get',
})

getParameters.definition = {
    methods: ["get","head"],
    url: '/api/job-parameter-values',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
getParameters.url = (options?: RouteQueryOptions) => {
    return getParameters.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
getParameters.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getParameters.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
getParameters.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getParameters.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
    const getParametersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getParameters.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
        getParametersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getParameters.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\WorkPhaseAssignmentController::getParameters
 * @see app/Http/Controllers/WorkPhaseAssignmentController.php:218
 * @route '/api/job-parameter-values'
 */
        getParametersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getParameters.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getParameters.form = getParametersForm
const WorkPhaseAssignmentController = { index, list, updateParameters, getParameters }

export default WorkPhaseAssignmentController