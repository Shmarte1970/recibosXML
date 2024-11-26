import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatCheckboxModule } from '@angular/material/checkbox';

@Component({
  selector: 'app-componente-checkbox',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, MatCheckboxModule],
  templateUrl: './checkbox.component.html',
  styleUrls: ['./checkbox.component.css']
})
export class ComponenteCheckboxComponent {
  @Input() control!: string
  @Input() name!: string
  @Input() form!: FormGroup
}
