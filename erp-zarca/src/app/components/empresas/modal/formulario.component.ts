import { AngularMaterialModule } from '../../../module/app.angular.material.component';
import { CommonModule } from '@angular/common';
import { Component, EventEmitter, OnInit, Output, } from '@angular/core';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { EmpresasComponent } from '../empresas.component';
import { ModuloService } from '../../../services/modulo.service';
import { ComponenteInputComponent } from '../../componente/input/input.component';
import { ComponenteCheckboxComponent } from '../../componente/checkbox/checkbox.component';
import { ComponenteTextareaComponent } from '../../componente/textarea/textarea.component';
import { ComponenteSelectComponent } from '../../componente/select/select.component';
import { ContactoComponent } from './contacto/contacto.component';
import { DelegacionComponent } from './delegacion/delegacion.component';
import { zcAccion } from '../interface';
import { ExistValidator } from '../../../validator/validator.component';
import { FormaPagoComponent } from './formapago/formapago.component';

@Component({
  selector: 'app-empresa-modal',
  standalone: true,
  imports: [
    AngularMaterialModule,
    CommonModule,
    ReactiveFormsModule,
    ComponenteInputComponent,
    ComponenteCheckboxComponent,
    ComponenteTextareaComponent,
    ComponenteSelectComponent,
    ContactoComponent,
    DelegacionComponent,
    MatSlideToggleModule,
    FormaPagoComponent
  ],
  templateUrl: './formulario.component.html',
  styleUrls: ['./formulario.component.css', './formulario.component.scss']
})
export class ComponentFormularioComponent implements OnInit {
  
  @Output() CloseModal = new EventEmitter();
  @Output() DoModal = new EventEmitter();
  constructor(public Module: EmpresasComponent, public module: ModuloService) { }
  openModal: boolean = false
  Accion: zcAccion = "Crear"
  Close() { this.CloseModal.emit() }
  ngOnInit(): void {
    if (this.Module.sData) this.Module.EmpresaForm.patchValue(this.Module.sData)
    else this.Module.EmpresaForm.reset()
    this.Module.EmpresaForm.controls["cifEmpresa"].clearValidators()
    this.Module.EmpresaForm.controls["cifEmpresa"].addValidators([Validators.required, Validators.maxLength(9), Validators.minLength(9),
    Validators.pattern(/^([A-Z]+){1}([0-9]){8}|([0-9]){8}([A-Z]+){1}$/), ExistValidator.ExistValidator(this.Module.EmpresaForm.controls["cifEmpresa"].value, this.module.cacheTable[this.Module.cacheList[0]].data.rows.map((a: any) => a.cifEmpresa))])
    if (this.Module.Accion === "Crear") {
      this.Module.EmpresaForm.patchValue({ userId: JSON.parse(sessionStorage.getItem('userData') || "")?.user.id, envioFacturas: 1 })
    }
  }
  columnsInfo: { Key: string, Name: string }[] = [
    { Key: "nombreContacto", Name: "Num Contrato" },
    { Key: "fechaContrato", Name: "Fecha Contrato" },
    { Key: "Importe", Name: "Importe" },
  ]
}