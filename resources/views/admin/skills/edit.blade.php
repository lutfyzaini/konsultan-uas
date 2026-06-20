<h1>Edit Skill</h1>

<form action="{{ route('admin.skills.update', $Skiil->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <input type="text"
           name="name"
           value="{{ $skill->name }}">

    <button type="submit">
        Update
    </button>
</form>