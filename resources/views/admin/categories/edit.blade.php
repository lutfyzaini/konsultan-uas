<h1>Edit Kategori</h1>

<form action="{{ route('admin.categories.update', $category->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <input type="text"
           name="name"
           value="{{ $category->name }}">

    <button type="submit">
        Update
    </button>
</form>