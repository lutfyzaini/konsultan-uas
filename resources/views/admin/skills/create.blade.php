<h1>Tambah Skill</h1>

<form action="{{ route('admin.skills.store') }}" method="POST">
    @csrf

    <input type="text"
           name="name"
           placeholder="Nama Skill">

    <button type="submit">
        Simpan
    </button>
</form>