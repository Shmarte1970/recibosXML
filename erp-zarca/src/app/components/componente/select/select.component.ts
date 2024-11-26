import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AbstractControl, FormBuilder, FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { ModuloService } from '../../../services/modulo.service';
import { ComponenteInputComponent } from '../input/input.component';
import { AngularMaterialModule } from '../../../module/app.angular.material.component';
import { ErrorMsgService } from '../../../services/errormsg.service';
import { ExistValidator } from '../../../validator/validator.component';

@Component({
  selector: 'app-componente-select',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, ComponenteInputComponent, AngularMaterialModule],
  templateUrl: './select.component.html',
  styleUrls: ['./select.component.css']
})
export class ComponenteSelectComponent implements OnInit {
  @Input() control!: string
  @Input() name!: string
  @Input() placeholder!: string
  @Input() form!: FormGroup
  @Input() table!: string
  @Input() dep!: string
  @Input() id!: string
  @Input() value!: string
  @Input() value2!: string
  @Input() add!: boolean //button de añadir en la tabla intermedia
  @Input() mayuscula: boolean = false //texto siempre mayuscula
  @Input() repetir: boolean = false //texto no puede repetir
  @Input() nulo: boolean = false //opción nulo
  @Output() changex = new EventEmitter()
  openModal: boolean = false
  constructor(public module: ModuloService, private formBuilder: FormBuilder, public error: ErrorMsgService) { }
  SelectForm!: FormGroup

  ngOnInit(): void {
    this.SelectForm = this.formBuilder.group({ [this.value]: this.formBuilder.control("") })
  }

  async Crear() {
    if (this.SelectForm.status === "VALID") {
      if (this.dep) this.SelectForm.addControl(this.dep, new FormControl(this.form.controls[this.dep].value))
      const data = await this.module.ModuloTableCreateElement(this.table, this.SelectForm.getRawValue())
      await this.module.UpdateModuleTable(this.table)
      this.form.patchValue({ [this.control]: data[this.id] })
      this.SelectForm.controls[this.value].clearValidators()
      this.openModal = false;
    }
  }

  OpenModal() {
    this.SelectForm.patchValue({ [this.value]: '' });
    this.SelectForm.controls[this.value].addValidators([Validators.required, ExistValidator.ExistValidator(this.SelectForm.controls[this.value].value, this.module.cacheTable[this.table].data.rows.map((a: any) => a.name))])
    this.openModal = true
  }
  get c(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }
  SetValue() {
    this.SelectForm.patchValue({ [this.value]: this.SelectForm.controls[this.value].value.trim().toUpperCase() })
  }
}
