@include('admin.posts._editor-form', [
    'post'       => null,
    'type'       => 'post',
    'formAction' => route('admin.posts.store'),
    'formMethod' => 'POST',
    'backRoute'  => route('admin.posts.index'),
    'pageTitle'  => 'Nuevo post',
])
