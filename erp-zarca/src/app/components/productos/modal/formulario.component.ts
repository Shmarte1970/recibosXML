import { AngularMaterialModule } from '../../../module/app.angular.material.component';
import { CommonModule } from '@angular/common';
import { Component, EventEmitter, OnInit, Output, } from '@angular/core';
import { ReactiveFormsModule, Validators } from '@angular/forms';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { ProductosComponent } from '../productos.component';
import { ProductosService } from '../../../services/productos.service';
import { ComponenteInputComponent } from '../../componente/input/input.component';
import { ComponenteCheckboxComponent } from '../../componente/checkbox/checkbox.component';
import { ComponenteTextareaComponent } from '../../componente/textarea/textarea.component';
import { ComponenteSelectComponent } from '../../componente/select/select.component';
import { zcAccion } from '../interface';
import { ExistValidator } from '../../../validator/validator.component';


@Component({
  selector: 'app-producto-modal',
  standalone: true,
  imports: [
    AngularMaterialModule,
    CommonModule,
    ReactiveFormsModule,
    ComponenteInputComponent,
    ComponenteCheckboxComponent,
    ComponenteTextareaComponent,
    ComponenteSelectComponent,    
    MatSlideToggleModule   
  ],

  templateUrl: './formulario.component.html',
  styleUrl: './formulario.component.css',
})


export class componenteFormularioComponent implements OnInit {

  @Output() CloseModal = new EventEmitter();
  @Output() DoModal = new EventEmitter();

  constructor(public Module: ProductosComponent, public module: ProductosService) { }
  openModal: boolean = false
  Accion:zcAccion = "Crear"
  Close() {this.CloseModal.emit()}
  ngOnInit(): void {
    if (this.Module.sData) this.Module.ProductoForm.patchValue(this.Module.sData)
      else this.Module.ProductoForm.reset()
    this.Module.ProductoForm.controls["matricula"].clearValidators()
    this.Module.ProductoForm.controls["matricula"].addValidators([Validators.required, Validators.maxLength(9), Validators.minLength(9),
    Validators.pattern(/^([A-Z]+){1}([0-9]){8}|([0-9]){8}([A-Z]+){1}$/), ExistValidator.ExistValidator(this.Module.ProductoForm.controls
      ["matricula"].value, this.module.cacheTable[this.Module.cacheList[0]].data.rows.maps((a: any) => a.matricula))])
  /*    if (this.Module.Accion === "Crear") {
        this.Module.ProductoForm.patchValue({ userId: JSON.parse(sessionStorage.getItem('userData'))})
      }*/ 
  }
}
