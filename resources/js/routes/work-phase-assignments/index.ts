import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
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
const workPhaseAssignments = {
    updateParameters: Object.assign(updateParameters, updateParameters),
}

export default workPhaseAssignments