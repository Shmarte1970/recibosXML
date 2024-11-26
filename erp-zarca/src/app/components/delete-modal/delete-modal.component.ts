import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { UserService } from '../../services/users.service';

@Component({
  selector: 'app-delete-modal',
  standalone: true,
  imports: [],
  templateUrl: './delete-modal.component.html',
  styleUrl: './delete-modal.component.css',
})
export class DeleteModalComponent implements OnInit {
  @Output()
  closeModal: EventEmitter<void>;

  @Output()
  deleteResource: EventEmitter<void>;

  @Input()
  resource: string = '';

  @Input()
  resourceId: number = 0;

  constructor(public userService: UserService) {
    this.closeModal = new EventEmitter<void>();
    this.deleteResource = new EventEmitter<void>();
  }

  ngOnInit(): void {}

  close() {
    this.closeModal.emit();
  }

  delete() {
    this.deleteResource.emit();
  }
}
