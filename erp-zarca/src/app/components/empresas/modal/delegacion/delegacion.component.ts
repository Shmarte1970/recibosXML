import { AngularMaterialModule } from '../../../../module/app.angular.material.component';
import { CommonModule } from '@angular/common';
import { Component, OnInit, } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ModuloService } from '../../../../services/modulo.service';
import { EmpresasComponent } from '../../empresas.component';
import { ComponenteInputComponent } from '../../../componente/input/input.component';
import { zcAccion } from '../../interface';
import { ComponenteCheckboxComponent } from '../../../componente/checkbox/checkbox.component';
import { ComponenteSelectComponent } from '../../../componente/select/select.component';
import { ToastrService } from 'ngx-toastr';


@Component({
  selector: 'app-empresa-tabla-delegacion',
  standalone: true,
  imports: [
    AngularMaterialModule,
    CommonModule,
    ReactiveFormsModule,
    ComponenteInputComponent,
    ComponenteCheckboxComponent,
    ComponenteSelectComponent
  ],
  templateUrl: './delegacion.component.html',
  styleUrls: ['./delegacion.component.css']
})
export class DelegacionComponent implements OnInit {
  constructor(
    public module: ModuloService,
    public Module: EmpresasComponent,
    private formBuilder: FormBuilder,
    private toastr: ToastrService
  ) { }
  columns: { Key: string, Name: string }[] = [
    { Key: "idDelegacion", Name: "id" },
    { Key: "nombreDelegacion", Name: "Nombre" },
  ]
  columns_comercial: { Key: string, Name: string }[] = [
    { Key: "nombreComercial", Name: "Nombre" },
    { Key: "apellidosComercial", Name: "Apellido" },
    { Key: "phoneOneComercial", Name: "Movil" },
  ]
  columns_contacto: { Key: string, Name: string }[] = [
    { Key: "nombreContacto", Name: "Nombre" },
    { Key: "apellidosContacto", Name: "Apellido" },
    { Key: "phoneOneContacto", Name: "Movil" },
  ]

  sData!: any
  sData_Comercial!: any
  sData_Contacto!: any

  openModal: boolean = false
  openModal_Comercial: boolean = false
  openModal_Contacto: boolean = false
  Accion: zcAccion = "Crear"
  Accion_Comercial: zcAccion = "Crear"
  Accion_Contacto: zcAccion = "Crear"

  DelegacionForm!: FormGroup
  ComercialForm!: FormGroup
  ContactoForm!: FormGroup
  DelegacionEmpresaForm!: FormGroup

  filtro!: any
  filtro_comercial!: any
  filtro_contacto!: any

  ngOnInit(): void {
    this.DelegacionForm = this.formBuilder.group({
      "idDelegacion": this.formBuilder.control(""),
      "nombreDelegacion": this.formBuilder.control("", Validators.required),
      "envioFacturas": this.formBuilder.control(""),
      "zcurbanaIdurbana": this.formBuilder.control("", Validators.required),
      "direccionFiscalDelegacion": this.formBuilder.control("", Validators.required),
      "postcodeId": this.formBuilder.control("", Validators.required),
      "cityId": this.formBuilder.control("", Validators.required),
      "provinceId": this.formBuilder.control("", Validators.required),
      "countryId": this.formBuilder.control("", Validators.required),
      "telefonoDelegacion": this.formBuilder.control("", Validators.required),
      "telefono2Delegacion": this.formBuilder.control(""),
      "emailDelegacion": this.formBuilder.control("", Validators.email),
      "palabraclaveDelegacion": this.formBuilder.control(""),
      "horarioDelegacion": this.formBuilder.control(""),
      "enable": this.formBuilder.control(1),
      "userId": this.formBuilder.control(""),
      idEmpresa: this.formBuilder.control(this.Module.EmpresaForm.controls["idEmpresa"].value),
      urbanaDelegacion: this.formBuilder.control(""),
      poblacionDelegacion: this.formBuilder.control(""),
      cpostalDelegacion: this.formBuilder.control(""),
      provinciaDelegacion: this.formBuilder.control(""),
      paisDelegacion: this.formBuilder.control(""),
    })
    this.DelegacionEmpresaForm = this.formBuilder.group({
      "idzcEmpresa": this.formBuilder.control(this.Module.EmpresaForm.controls["idEmpresa"].value),
      "idzcDelegacion": this.formBuilder.control(""),
    })
    this.ComercialForm = this.formBuilder.group({
      "idzccomercial": this.formBuilder.control("", Validators.required),
      "idzcDelegacion": this.formBuilder.control(""),
    })
    this.ContactoForm = this.formBuilder.group({
      "idzccontacto": this.formBuilder.control("", Validators.required),
      "idzcDelegacion": this.formBuilder.control(""),
    })
    this.filtro = this.module.cacheTable["zcempresazcdelegacion"].data.rows.filter((a: any) => a.idzcEmpresa == this.Module.EmpresaForm.controls["idEmpresa"].value)
  }


  Filtrar(data: any) {
    return Boolean(this.filtro.find((a: any) => a.idzcDelegacion == data.idDelegacion))
  }
  Filtrar_comercial(data: any) {
    return Boolean(this.filtro_comercial.find((a: any) => a.idzccomercial == data.idcomercial))
  }

  Filtrar_contacto(data: any) {
    return Boolean(this.filtro_contacto.find((a: any) => a.idzccontacto == data.idContacto))
  }

  async OpenModal() {
    try {
      if (this.Accion === "Crear") {
        this.DelegacionForm.reset()
        //this.DelegacionForm.patchValue({ envioFacturas: 1 })
        this.openModal = true
      } else if (this.Accion === "Editar") {
        this.DelegacionForm.patchValue(this.sData)
        this.filtro_comercial = this.module.cacheTable["zcdelegacionzccomercial"].data.rows.filter((a: any) => a.idzcDelegacion == this.DelegacionForm.controls["idDelegacion"].value)
        this.filtro_contacto = this.module.cacheTable["zcdelegacionzccontacto"].data.rows.filter((a: any) => a.idzcDelegacion == this.DelegacionForm.controls["idDelegacion"].value)
        this.openModal = true
      } else if (this.Accion === "Activar") {
        this.sData.enable = !this.sData.enable
        this.DelegacionForm.patchValue(this.sData)
        await this.module.ModuloTableUpdateElement("zcdelegacion", this.DelegacionForm.getRawValue())
        this.DoModal()
      }
    } catch (e) { console.log(e) }
  }

  async DoModal() {
    try {
      if (this.Accion === "Crear") {
        if (this.DelegacionForm.status === "VALID") {
          this.DelegacionForm.patchValue({ userId: JSON.parse(sessionStorage.getItem('userData') || "")?.user.id, enable: 1 })
          const d = await this.module.ModuloTableCreateElement("zcdelegacion", this.DelegacionForm.getRawValue())
          this.DelegacionEmpresaForm.patchValue({ idzcDelegacion: d.idDelegacion })
          await this.module.ModuloTableCreateElement("zcempresazcdelegacion", this.DelegacionEmpresaForm.getRawValue())
          await this.module.UpdateModuleTable("zcempresazcdelegacion")
          await this.module.UpdateModuleTable("zcdelegacionzccontacto")
          this.toastr.success(`Acción realizado correctamente!`);
          this.openModal = false
          this.DelegacionForm.reset()
        } else this.DelegacionForm.markAllAsTouched()
      } else if (this.Accion === "Editar") {
        if (this.DelegacionForm.status === "VALID") {
          await this.module.ModuloTableUpdateElement("zcdelegacion", this.DelegacionForm.getRawValue())
          this.toastr.success(`Acción realizado correctamente!`);
          this.openModal = false
          this.DelegacionForm.reset()
        } else this.DelegacionForm.markAllAsTouched()
      }
      await this.module.UpdateModuleTable("zcdelegacion")
      this.filtro = this.module.cacheTable["zcempresazcdelegacion"].data.rows.filter((a: any) => a.idzcEmpresa == this.Module.EmpresaForm.controls["idEmpresa"].value)
    } catch (e) { console.log(e) }
  }

  async OpenModal_Comercial() {
    this.ComercialForm.patchValue({ idzcDelegacion: this.sData.idDelegacion })
    if (this.Accion_Comercial === "Asignar") {
      this.openModal_Comercial = true
    } else if (this.Accion_Comercial === "Desasignar") {
      this.DoModal_Comercial()
    }
  }

  async DoModal_Comercial() {
    try {
      if (this.Accion_Comercial === "Asignar") {
        if (this.ComercialForm.status === "VALID") {
          await this.module.ModuloTableCreateElement("zcdelegacionzccomercial", this.ComercialForm.getRawValue())
          this.toastr.success(`Acción realizado correctamente!`);
          this.openModal_Comercial = false
        } else this.ComercialForm.markAllAsTouched()
      } else if (this.Accion_Comercial === "Desasignar") {
        if (this.ComercialForm.status === "VALID") {
          const d = this.module.cacheTable["zcdelegacionzccomercial"].data.rows.filter((a: any) => a.idzccomercial === this.sData_Comercial.idcomercial).filter((a: any) => a.idzcDelegacion === this.sData.idDelegacion)
          for (const c of d) await this.module.ModuloTableDeleteElement("zcdelegacionzccomercial", { idzcDelegacionzcComercial: c.idzcDelegacionzcComercial })
          this.toastr.success(`Acción realizado correctamente!`);
          this.openModal_Comercial = false
        } else this.ComercialForm.markAllAsTouched()
      }
      await this.module.UpdateModuleTable("zcdelegacionzccomercial")
      this.filtro_comercial = this.module.cacheTable["zcdelegacionzccomercial"].data.rows.filter((a: any) => a.idzcDelegacion == this.DelegacionForm.controls["idDelegacion"].value)
    } catch (e) { }
  }

  async OpenModal_Contacto() {
    this.ContactoForm.patchValue({ idzcDelegacion: this.sData.idDelegacion })
    if (this.Accion_Contacto === "Asignar") {
      this.openModal_Contacto = true
    } else if (this.Accion_Contacto === "Desasignar") {
      this.DoModal_Contacto()
    }
  }

  async DoModal_Contacto() {
    try {
      if (this.Accion_Contacto === "Asignar") {
        if (this.ContactoForm.status === "VALID") {
          await this.module.ModuloTableCreateElement("zcdelegacionzccontacto", this.ContactoForm.getRawValue())
          this.toastr.success(`Acción realizado correctamente!`);
          this.openModal_Contacto = false
        } else this.ContactoForm.markAllAsTouched()
      } else if (this.Accion_Contacto === "Desasignar") {
        if (this.ContactoForm.status === "VALID") {
          const d = this.module.cacheTable["zcdelegacionzccontacto"].data.rows.filter((a: any) => a.idzccontacto === this.sData_Contacto.idContacto).filter((a: any) => a.idzcDelegacion === this.sData.idDelegacion)
          for (const c of d) await this.module.ModuloTableDeleteElement("zcdelegacionzccontacto", { idzcDelegacionzcContacto: c.idzcDelegacionzcContacto })
          this.toastr.success(`Acción realizado correctamente!`);
          this.openModal_Contacto = false
        } else this.ContactoForm.markAllAsTouched()
      }
      await this.module.UpdateModuleTable("zccontactos")
      await this.module.UpdateModuleTable("zcdelegacionzccontacto")
      this.filtro_contacto = this.module.cacheTable["zcdelegacionzccontacto"].data.rows.filter((a: any) => a.idzcDelegacion == this.DelegacionForm.controls["idDelegacion"].value)
    } catch (e) { }
  }
}