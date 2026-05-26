@include('admin.posts._editor-form', [
    'post'       => null,
    'type'       => 'page',
    'formAction' => route('admin.pages.store'),
    'formMethod' => 'POST',
    'backRoute'  => route('admin.pages.index'),
    'pageTitle'  => 'Nueva página',
])
