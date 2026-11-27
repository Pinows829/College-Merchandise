@extends('layouts.app')

@section('title', $product->exists ? 'Edit product' : 'Add product')

@section('content')
<section class="page-intro"><div><p class="eyebrow">CATALOG</p><h1>{{ $product->exists ? 'Edit product' : 'Add a product' }}</h1><p class="muted">Set its college, course collection, details, and stock.</p></div><a class="btn alt" href="{{ route('admin.products.index') }}">Back to products</a></section>
<form class="form card product-editor" method="post" action="{{ $product->exists ? route('admin.products.update',$product) : route('admin.products.store') }}">@csrf @if($product->exists)@method('PUT')@endif
    @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <label>Product name</label><input name="name" maxlength="160" value="{{ old('name',$product->name) }}" required>
    <div class="form-split"><div><label>College</label><select id="product-college" name="college_id" required><option value="">Select college</option>@foreach($colleges as $college)<option value="{{ $college->id }}" @selected((string)old('college_id',$product->college_id)===(string)$college->id)>{{ $college->code }} — {{ $college->name }}</option>@endforeach</select></div><div><label>Course collection</label><select id="product-course" name="course_id"><option value="">College-wide merchandise</option>@foreach($courses as $course)<option data-college="{{ $course->college_id }}" value="{{ $course->id }}" @selected((string)old('course_id',$product->course_id)===(string)$course->id)>{{ $course->name }}</option>@endforeach</select></div></div>
    <label>Category</label><select name="category">@foreach(['Shirts','Jackets','Accessories'] as $category)<option value="{{ $category }}" @selected(old('category',$product->category)===$category)>{{ $category }}</option>@endforeach</select>
    <label>Price (₱)</label><input type="number" name="price" min="0" step="0.01" value="{{ old('price',$product->price) }}" required>
    <label>Description</label><textarea name="description" rows="4" required>{{ old('description',$product->description) }}</textarea>
    <label>Sizes and stock</label><textarea name="sizes" rows="5" placeholder="S:10&#10;M:10&#10;L:8" required>{{ old('sizes',$sizesText) }}</textarea><small class="field-help">Enter one size per line, like <b>S:10</b>. Unlisted sizes keep their existing stock.</small>
    <button class="full" type="submit">Save product</button>
</form>
<script>
const collegeSelect=document.getElementById('product-college');
const courseSelect=document.getElementById('product-course');
function filterCourses(){const id=collegeSelect.value;[...courseSelect.options].forEach(option=>{const allowed=!option.dataset.college||option.dataset.college===id;option.hidden=!allowed;if(!allowed&&option.selected)courseSelect.value='';});}
collegeSelect.addEventListener('change',filterCourses);filterCourses();
</script>
@endsection
