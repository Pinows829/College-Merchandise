@extends('layouts.app')

@section('title', 'Course collections')

@section('content')
<section class="page-intro"><div><p class="eyebrow">COLLECTIONS</p><h1>Course collections</h1><p class="muted">Add courses students can shop by inside each college.</p></div></section>
<div class="admin-columns course-admin-columns"><form class="card course-editor" method="post" action="{{ route('admin.courses.store') }}">@csrf<p class="eyebrow">NEW COLLECTION</p><h2>Add a course</h2>
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <label>College</label><select name="college_id" required><option value="">Select college</option>@foreach($colleges as $college)<option value="{{ $college->id }}" @selected((string)old('college_id')===(string)$college->id)>{{ $college->code }} — {{ $college->name }}</option>@endforeach</select>
    <label>Course name</label><input name="name" maxlength="180" value="{{ old('name') }}" placeholder="e.g. Bachelor of Science in Information Technology (BSIT)" required>
    <button class="full" type="submit">Add course</button></form>
    <section class="panel course-list-panel"><div class="panel-heading"><div><p class="eyebrow">AVAILABLE FILTERS</p><h2>Courses</h2></div><span class="result-count">{{ $courses->count() }} listed</span></div>
        @if($courses->isEmpty())<div class="empty-state compact"><span>＋</span><b>No course collections yet</b><p>Add course names to make them available in the shop.</p></div>@else<ul class="course-list">@foreach($courses as $course)<li><span class="college-code-pill">{{ $course->college->code }}</span><span>{{ $course->name }}</span></li>@endforeach</ul>@endif
    </section></div>
@endsection
