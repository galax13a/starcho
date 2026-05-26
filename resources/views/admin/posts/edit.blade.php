@include('admin.posts._editor-form', [
    'type'       => 'post',
    'formAction' => route('admin.posts.update', $post),
    'formMethod' => 'PUT',
    'backRoute'  => route('admin.posts.index'),
    'pageTitle'  => 'Editar post',
])
