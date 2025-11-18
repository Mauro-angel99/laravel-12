import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
export const general = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: general.url(options),
    method: 'get',
})

general.definition = {
    methods: ["get","head"],
    url: '/settings/general',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
general.url = (options?: RouteQueryOptions) => {
    return general.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
general.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: general.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
general.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: general.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
    const generalForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: general.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
        generalForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: general.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:61
 * @route '/settings/general'
 */
        generalForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: general.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    general.form = generalForm
const settings = {
    general: Object.assign(general, general),
}

export default settings