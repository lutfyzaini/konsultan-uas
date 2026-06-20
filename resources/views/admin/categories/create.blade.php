<h1>Tambah Kategori</h1>

<form action="{{ route('admin.categories.store') }}" method="POST">
    @csrf

    <input type="text"
           name="name"
           placeholder="Nama Kategori">

    <button type="submit">
        Simpan
    </button>
</form>