import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/file-path-settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FilePathSettingController::index
 * @see app/Http/Controllers/FilePathSettingController.php:22
 * @route '/api/file-path-settings'
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
* @see \App\Http\Controllers\FilePathSettingController::update
 * @see app/Http/Controllers/FilePathSettingController.php:44
 * @route '/api/file-path-settings'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/file-path-settings',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\FilePathSettingController::update
 * @see app/Http/Controllers/FilePathSettingController.php:44
 * @route '/api/file-path-settings'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FilePathSettingController::update
 * @see app/Http/Controllers/FilePathSettingController.php:44
 * @route '/api/file-path-settings'
 */
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\FilePathSettingController::update
 * @see app/Http/Controllers/FilePathSettingController.php:44
 * @route '/api/file-path-settings'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FilePathSettingController::update
 * @see app/Http/Controllers/FilePathSettingController.php:44
 * @route '/api/file-path-settings'
 */
        updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\FilePathSettingController::updateFormatting
 * @see app/Http/Controllers/FilePathSettingController.php:87
 * @route '/api/file-path-settings/formatting'
 */
export const updateFormatting = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateFormatting.url(options),
    method: 'put',
})

updateFormatting.definition = {
    methods: ["put"],
    url: '/api/file-path-settings/formatting',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\FilePathSettingController::updateFormatting
 * @see app/Http/Controllers/FilePathSettingController.php:87
 * @route '/api/file-path-settings/formatting'
 */
updateFormatting.url = (options?: RouteQueryOptions) => {
    return updateFormatting.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FilePathSettingController::updateFormatting
 * @see app/Http/Controllers/FilePathSettingController.php:87
 * @route '/api/file-path-settings/formatting'
 */
updateFormatting.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateFormatting.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\FilePathSettingController::updateFormatting
 * @see app/Http/Controllers/FilePathSettingController.php:87
 * @route '/api/file-path-settings/formatting'
 */
    const updateFormattingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateFormatting.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FilePathSettingController::updateFormatting
 * @see app/Http/Controllers/FilePathSettingController.php:87
 * @route '/api/file-path-settings/formatting'
 */
        updateFormattingForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateFormatting.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateFormatting.form = updateFormattingForm
const filePathSettings = {
    index: Object.assign(index, index),
update: Object.assign(update, update),
updateFormatting: Object.assign(updateFormatting, updateFormatting),
}

export default filePathSettings