import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/non-conformities',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\NonConformityController::index
 * @see app/Http/Controllers/NonConformityController.php:15
 * @route '/api/non-conformities'
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
const nonConformities = {
    index: Object.assign(index, index),
}

export default nonConformities