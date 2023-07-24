@extends('layouts.admin.master')

@section('title')
    Default Forms
    {{-- {{ $title }} --}}
@endsection

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select2.css') }}">
@endpush

@section('content')


    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 col-xl-12">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h5>Mack (Controller - Model - Migrate - Validation)</h5>
                            </div>
                            <div class="card-body">
                                <form class="theme-form" method="post" action="{{ route('makeTableRequest') }}">
                                    @csrf
                                    <div class="mb-3 col-xl-2">
                                        <label class="col-form-label pt-0" for="exampleName"> Model Name (samill)</label>
                                        <input class="form-control" id="exampleName" type="text" required
                                            aria-describedby="emailHelp" placeholder="user" name="tableNameSingel" />

                                    </div>

                                    <div class="row element" id="div_0">
                                        <div class="mb-3 col-xl-2">
                                            <label class="col-form-label pt-0" for="exampleName">Name</label>
                                            <input class="form-control" id="exampleName" required type="text"
                                                aria-describedby="emailHelp" placeholder="" name="name[0]" />

                                        </div>

                                        <div class="mb-3 col-xl-2">
                                            <label class="col-form-label pt-0">Type</label>
                                            <select required class="js-example-placeholder-multiple col-sm-12"
                                                name="type[0]">
                                                <option value="integer">integer</option>
                                                <option value="string">string</option>
                                                <option value="image">image</option>
                                                {{-- <option value="enum">enum</option>
                                                <option value="json">json</option> --}}
                                            </select>

                                        </div>

                                        <div class="mb-3 col-xl-2">
                                            <label class="col-form-label pt-0">Default</label>
                                            <input class="form-control" type="text" aria-describedby="emailHelp"
                                                placeholder="Defult" name="default[0]" />

                                        </div>


                                        <div class="mb-3 col-xl-1">
                                            <label class="col-form-label pt-0 d-flex"
                                                for="exampleCheckBoxUnique">unique</label>
                                            <input id="exampleCheckBoxUnique" type="checkbox" name="unique[0]" />
                                        </div>


                                        <div class="mb-3 col-xl-1">
                                            <label class="col-form-label pt-0 d-flex" for="exampleCheckBoxNull">null</label>
                                            <input id="exampleCheckBoxNull" type="checkbox" name="null[0]" />
                                        </div>

                                        <div class="col-2">
                                            <div class="mb-3">
                                                <span class="add btn btn-primary">اضافة</span>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="checkbox p-0">
                                        <input id="RememberToken" type="checkbox" name="RememberToken" />
                                        <label class="mb-0" for="RememberToken">RememberToken</label>
                                    </div>

                                    <div class="checkbox p-0">
                                        <input id="Timestamps" type="checkbox" name="Timestamps" />
                                        <label class="mb-0" for="Timestamps">Timestamps</label>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/bootstrap/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/bootstrap/bootstrap.min.js') }}"></script>

        <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
        <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>


        <script>
            $(document).ready(function() {
                // Add new element
                $(".add").click(function() {
                    // Finding total number of elements added
                    var total_element = $(".element").length;

                    // last <> with element class id
                    var lastid = $(".element:last").attr("id");
                    var split_id = lastid.split("_");
                    var nextindex = Number(split_id[1]) + 1;

                    // Adding new div container after last occurance of element class
                    $(".element:last").after(

                        "<div class='element row' id='div_" + nextindex + "'></div>"
                    );

                    // Adding element to <div>
                    $("#div_" + nextindex).append(
                        `
                        <div class="mb-3 col-xl-2">
                            <label class="col-form-label pt-0" for="exampleInputEmail1">Name</label>
                            <input class="form-control" type="text"
                                aria-describedby="emailHelp" placeholder="" name="name[${nextindex}]"/>

                        </div>

                        <div class="mb-3 col-xl-2">
                            <label class="col-form-label pt-0" for="exampleInputEmail1">Type</label>
                            <select class="js-example-placeholder-multiple col-sm-12" name="type[${nextindex}]">
                                <option value="integer">integer</option>
                                <option value="string">string</option>
                                <option value="image">image</option>
                            </select>

                        </div>

                        <div class="mb-3 col-xl-2">
                            <label class="col-form-label pt-0" for="exampleInputEmail1">Default</label>
                            <input class="form-control" type="text"
                                aria-describedby="emailHelp" placeholder="Defult" name="default[${nextindex}]" />

                        </div>


                        <div class="mb-3 col-xl-1">
                            <label class="col-form-label pt-0 d-flex"
                                for="exampleInputEmail1">unique</label>
                            <input id="dafault-checkbox" type="checkbox" name="unique[${nextindex}]" />
                        </div>


                        <div class="mb-3 col-xl-1">
                            <label class="col-form-label pt-0 d-flex" for="exampleInputEmail1">null</label>
                            <input id="dafault-checkbox" type="checkbox" name="null[${nextindex}]"/>
                        </div>

                        <div class="col-2">
                            <div class="mb-3">
                                <span id="remove_${nextindex}" class="remove btn btn-danger">حذف</span>
                            </div>
                        </div>





                    </div>`
                    );
                });

                // Remove element
                $(".row").on("click", ".remove", function() {
                    var id = this.id;
                    var split_id = id.split("_");
                    var deleteindex = split_id[1];
                    // Remove <div> with id
                    $("#div_" + deleteindex).remove();
                });
            });
        </script>
    @endpush
@endsection
