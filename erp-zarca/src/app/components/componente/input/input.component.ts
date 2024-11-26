import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AbstractControl, FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AngularMaterialModule } from '../../../module/app.angular.material.component';
import { ErrorMsgService } from '../../../services/errormsg.service';

@Component({
  selector: 'app-componente-input',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, AngularMaterialModule],
  templateUrl: './input.component.html',
  styleUrls: ['./input.component.css']
})
export class ComponenteInputComponent {
  @Input() control!: string
  @Input() name!: string
  @Input() placeholder!: string
  @Input() form!: FormGroup
  @Output() input = new EventEmitter()

  constructor(public error: ErrorMsgService) { }

  get c(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }

}
