import { AngularMaterialModule } from '../../../../module/app.angular.material.component';
import { CommonModule } from '@angular/common';
import { Component, OnInit, } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ModuloService } from '../../../../services/modulo.service';
import { EmpresasComponent } from '../../empresas.component';
import { zcAccion } from '../../interface';
import { ComponenteInputComponent } from '../../../componente/input/input.component';
import { ComponenteTextareaComponent } from '../../../componente/textarea/textarea.component';
import { ComponenteSelectComponent } from '../../../componente/select/select.component';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-empresa-tabla-contacto',
  standalone: true,
  imports: [
    AngularMaterialModule,
    CommonModule,
    ReactiveFormsModule,
    ComponenteInputComponent,
    ComponenteTextareaComponent,
    ComponenteSelectComponent,
  ],
  templateUrl: './contacto.component.html',
  styleUrls: ['./contacto.component.css']
})
export class ContactoComponent implements OnInit {
  constructor(
    public module: ModuloService,
    public Module: EmpresasComponent,
    private formBuilder: FormBuilder,
    private toastr: ToastrService
  ) { }

  sData!: any
  sData_delegacion!: any

  openModal: boolean = false

  Accion: zcAccion = "Crear"

  ContactoForm!: FormGroup

  filtro!: any
  filtro_delegacion!: any

  columns: { Key: string, Name: string }[] = [
    { Key: "nombreContacto", Name: "Nombre" },
    { Key: "apellidosContacto", Name: "Apellido" },
    { Key: "phoneOneContacto", Name: "Móvil" },
  ]
  ngOnInit(): void {
    this.ContactoForm = this.formBuilder.group({
      "idContacto": this.formBuilder.control(""),
      "nombreContacto": this.formBuilder.control("", Validators.required),
      "apellidosContacto": this.formBuilder.control("", Validators.required),
      "phoneOneContacto": this.formBuilder.control("", Validators.required),
      "phoneTwoContacto": this.formBuilder.control(""),
      "emailContacto": this.formBuilder.control("", Validators.email),
      "anotacionesContacto": this.formBuilder.control(""),
      "horarioContacto": this.formBuilder.control(""),
      "idCargo": this.formBuilder.control("", Validators.required),
      "nomCargo": this.formBuilder.control(""),
      "delegacion": this.formBuilder.control("", Validators.required),
    })
    this.UpdateFiltro()
  }

  Filtrar(data: any) {
    return Boolean(this.filtro.find((a: any) => a.idzccontacto == data.idContacto))
  }

  Filtrar_Delegacion(data: any) {
    return Boolean(this.filtro_delegacion.find((a: any) => a.idzcDelegacion == data.idDelegacion))
  }

  OpenModal() {
    this.UpdateFiltro()
    if (this.Accion === "Crear") {
      this.ContactoForm.reset()
    } else if (this.Accion === "Editar") {
      this.sData_delegacion = this.filtro.filter((a: any) => a.idzccontacto === this.sData.idContacto).map((a: any) => a.idzcDelegacion)
      this.ContactoForm.patchValue(this.sData)
      this.ContactoForm.patchValue({ delegacion: this.sData_delegacion })
    }
    this.openModal = true
  }

  async DoModal() {
    try {
      if (this.Accion === "Crear") {
        if (this.ContactoForm.status === "VALID") {
          const c = await this.module.ModuloTableCreateElement("zccontactos", this.ContactoForm.getRawValue())
          for (const d of this.ContactoForm.controls["delegacion"].value) {
            await this.module.ModuloTableCreateElement("zcdelegacionzccontacto", {
              idzccontacto: c.idContacto, idzcDelegacion: d
            })
          }
          this.ContactoForm.reset()
          this.openModal = false
        } else this.ContactoForm.markAllAsTouched()
      } else if (this.Accion === "Editar") {
        if (this.ContactoForm.status === "VALID") {
          const c = await this.module.ModuloTableUpdateElement("zccontactos", this.ContactoForm.getRawValue())
          const x = this.module.cacheTable["zcdelegacionzccontacto"].data.rows.filter((a: any) => Boolean(this.sData_delegacion.includes(a.idzcDelegacion))).filter((a: any) => a.idzccontacto == c.idContacto)
          for (const e of x) await this.module.ModuloTableDeleteElement("zcdelegacionzccontacto", { idzcDelegacionzcContacto: e.idzcDelegacionzcContacto })
          for (const d of this.ContactoForm.controls["delegacion"].value) {
            await this.module.ModuloTableCreateElement("zcdelegacionzccontacto", {
              idzccontacto: c.idContacto, idzcDelegacion: d
            })
          }
          this.ContactoForm.reset()
          this.openModal = false
        } else this.ContactoForm.markAllAsTouched()
      }
      await this.module.UpdateModuleTable("zccontactos")
      await this.module.UpdateModuleTable("zcdelegacionzccontacto")
      this.UpdateFiltro()
      this.toastr.success(`Acción realizado correctamente!`);
    } catch (e) { console.log(e) }
  }

  UpdateFiltro() {
    this.filtro_delegacion = this.module.cacheTable["zcempresazcdelegacion"].data.rows.filter((a: any) => a.idzcEmpresa == this.Module.EmpresaForm.controls["idEmpresa"].value)
    this.filtro = this.module.cacheTable["zcdelegacionzccontacto"].data.rows.filter((a: any) =>
      Boolean(this.filtro_delegacion.find((b: any) => b.idzcDelegacion === a.idzcDelegacion)))
  }
}