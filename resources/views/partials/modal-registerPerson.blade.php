<form action="{{ url('admin/ajax/person/store') }}" id="create-form-person" method="POST">
    <div class="modal fade" tabindex="-1" id="modal-create-person" role="dialog">
        <div class="modal-dialog modal-primary">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="color: #ffffff !important"><i class="voyager-plus" ></i> Registrar Persona</h4>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">Primer Nombre</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Juan" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">Segundo Nombre (Opcional)</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="Daniel">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">Apellido Paterno</label>
                            <input type="text" name="paternal_surname" class="form-control" placeholder="Perez" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">Apellido Materno</label>
                            <input type="text" name="maternal_surname" class="form-control" placeholder="Ortiz" >
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">NIT/CI</label>
                            <input type="text" name="ci" class="form-control" placeholder="123456789" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">Celular</label>
                            <input type="text" name="phone" class="form-control" placeholder="76558214">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">Género</label>
                            <select name="gender" id="gender" class="form-control select2" required>
                                <option value="" disabled selected>--Seleccione una opción--</option>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">F. Nacimiento</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Dirección</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="C/ 18 de nov. Nro 123 zona central"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <input type="submit" class="btn btn-primary btn-save-person" value="Guardar">
                </div>
            </div>
        </div>
    </div>
</form>
