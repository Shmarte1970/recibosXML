import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AbstractControl, FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AngularMaterialModule } from '../../../module/app.angular.material.component';
import { ErrorMsgService } from '../../../services/errormsg.service';

@Component({
  selector: 'app-componente-textarea',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, AngularMaterialModule],
  templateUrl: './textarea.component.html',
  styleUrls: ['./textarea.component.css']
})
export class ComponenteTextareaComponent {
  @Input() control!: string
  @Input() name!: string
  @Input() placeholder!: string
  @Input() form!: FormGroup
  @Input() rows!: number

  constructor(public error: ErrorMsgService) { }

  get c(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }

}
