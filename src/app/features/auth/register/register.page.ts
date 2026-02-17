import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonicModule, AlertController } from '@ionic/angular';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, IonicModule],
  templateUrl: './register.page.html',
  styleUrls: ['./register.page.scss']
})
export class RegisterPage {
  name = '';
  email = '';
  password = '';
  confirmPassword = '';
  showPassword = false;
  isLoading = false;

  constructor(
    private authService: AuthService,
    private router: Router,
    private alertController: AlertController
  ) {}

  togglePassword(): void {
    this.showPassword = !this.showPassword;
  }

  async register(): Promise<void> {
    if (!this.name || !this.email || !this.password) {
      this.showAlert('Error', 'Please fill in all fields');
      return;
    }

    if (this.password !== this.confirmPassword) {
      this.showAlert('Error', 'Passwords do not match');
      return;
    }

    if (this.password.length < 6) {
      this.showAlert('Error', 'Password must be at least 6 characters');
      return;
    }

    this.isLoading = true;
    
    this.authService.register({
      name: this.name,
      email: this.email,
      password: this.password,
      password_confirmation: this.confirmPassword
    }).subscribe({
      next: () => {
        this.isLoading = false;
        this.router.navigate(['/dashboard']);
      },
      error: (error: any) => {
        this.isLoading = false;
        const message = error.error?.message || 'Registration failed. Please try again.';
        this.showAlert('Error', message);
      }
    });
  }

  goToLogin(): void {
    this.router.navigate(['/login']);
  }

  private async showAlert(title: string, message: string): Promise<void> {
    const alert = await this.alertController.create({
      header: title,
      message: message,
      buttons: ['OK']
    });
    await alert.present();
  }
}
