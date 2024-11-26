import { AngularMaterialModule } from '../../../../module/app.angular.material.component';
import { CommonModule } from '@angular/common';
import { Component, Input, OnInit, } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ModuloService } from '../../../../services/modulo.service';
import { EmpresasComponent } from '../../empresas.component';
import { ComponenteInputComponent } from '../../../componente/input/input.component';
import { ComponenteTextareaComponent } from '../../../componente/textarea/textarea.component';
import { ComponenteSelectComponent } from '../../../componente/select/select.component';
import { ToastrService } from 'ngx-toastr';
import { zcAccion } from '../../interface';

@Component({
  selector: 'app-empresa-tabla-formapago',
  standalone: true,
  imports: [
    AngularMaterialModule,
    CommonModule,
    ReactiveFormsModule,
    ComponenteInputComponent,
    ComponenteTextareaComponent,
    ComponenteSelectComponent,
  ],
  templateUrl: './formapago.component.html',
  styleUrls: ['./formapago.component.css']
})
export class FormaPagoComponent implements OnInit {
  @Input() Type!: "alquiler" | "compra" | "venta"
  Accion: zcAccion = "Crear"
  sData!: any
  openModal: boolean = false
  filtro!: any
  FormaPagoForm!: FormGroup
  constructor(
    public module: ModuloService,
    public Module: EmpresasComponent,
    private formBuilder: FormBuilder,
    private toastr: ToastrService
  ) { }

  ngOnInit(): void {
    this.FormaPagoForm = this.formBuilder.group({
      "idPagosCobroEmp": this.formBuilder.control(""),
      "idEmpresa": this.formBuilder.control(""),
      "idFormaPago": this.formBuilder.control("", Validators.required),
      "descripcionFormaPago": this.formBuilder.control(""),
      "idVen": this.formBuilder.control("", Validators.required),
      "desVen": this.formBuilder.control(""),
      "diaPago": this.formBuilder.control("", Validators.required),
      "porcentaje": this.formBuilder.control("", Validators.required),
      "alquiler": this.formBuilder.control(""),
      "venta": this.formBuilder.control(""),
      "compra": this.formBuilder.control(""),
    })
    this.module.cacheTable["zcdiapago"] = { data: { rows: [] } }
    for (let i = 0; i <= 31; i++) this.module.cacheTable["zcdiapago"].data.rows.push({ dia: i })
    this.module.cacheTable["zcporcentaje"] = { data: { rows: [] } }
    for (let i of [5, 10, 15, 20, 25, 30, 50, 75, 100]) this.module.cacheTable["zcporcentaje"].data.rows.push({ porcentaje: i })
    this.UpdateFiltro()
  }

  OpenModal() {
    this.FormaPagoForm.reset()
    if (this.Accion === "Editar") this.FormaPagoForm.patchValue(this.sData)
    this.openModal = true
  }
  async DoModal() {
    try {
      if (this.Accion === "Asignar") {
        if (this.FormaPagoForm.status === "VALID") {
          const pago = this.module.cacheTable['zcformasdepago'].data.rows.find((a: any) => a.idFormasDePago == this.FormaPagoForm.controls['idFormaPago'].value)
          this.FormaPagoForm.patchValue({
            idEmpresa: this.Module.EmpresaForm.controls["idEmpresa"].value,
            descripcionFormaPago: pago.descripcionPagoCobro,
            [this.Type]: 1
          })
          await this.module.ModuloTableCreateElement("zcempresapagoscobro", this.FormaPagoForm.getRawValue())
          this.openModal = false
        } else this.FormaPagoForm.markAllAsTouched()
      } else if (this.Accion === "Editar") {
        if (this.FormaPagoForm.status === "VALID") {
          const pago = this.module.cacheTable['zcformasdepago'].data.rows.find((a: any) => a.idFormasDePago == this.FormaPagoForm.controls['idFormaPago'].value)
          this.FormaPagoForm.patchValue({
            descripcionFormaPago: pago.descripcionPagoCobro,
          })
          await this.module.ModuloTableUpdateElement("zcempresapagoscobro", this.FormaPagoForm.getRawValue())
          this.openModal = false
        } else this.FormaPagoForm.markAllAsTouched()
      } else if (this.Accion === "Desasignar") {
        await this.module.ModuloTableDeleteElement("zcempresapagoscobro", { idPagosCobroEmp: this.sData.idPagosCobroEmp })
      }
      await this.module.UpdateModuleTable("zcempresapagoscobro")
      this.UpdateFiltro()
      this.toastr.success(`Acción realizado correctamente!`);
    } catch (e) { console.log(e) }
  }

  findPago(id: number) {
    return this.module.cacheTable['zcformasdepago'].data.rows.find((a: any) => a.idFormasDePago === id)
  }
  UpdateFiltro() {
    this.filtro = this.module.cacheTable["zcempresapagoscobro"].data.rows.filter((a: any) =>
      a.idEmpresa === this.Module.EmpresaForm.controls["idEmpresa"].value && a[this.Type])
  }
}