@include('admin.posts._editor-form', [
    'type'       => 'page',
    'formAction' => route('admin.pages.update', $post),
    'formMethod' => 'PUT',
    'backRoute'  => route('admin.pages.index'),
    'pageTitle'  => 'Editar página',
])
