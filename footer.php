<!-- ══════════════ RICH FOOTER ══════════════ -->
<footer class="site-footer">
   <div class="footer-top">
      <div class="footer-grid">

         <!-- Brand Column -->
         <div class="footer-col brand-col">
            <div class="footer-logo">
               <img src="images/logo.png" alt="DineFlow" style="height:3.2rem;width:auto;">
            </div>
            <p class="footer-tagline">A complete restaurant workflow management system — built for speed, simplicity and smart operations.</p>
            <div class="footer-social">
               <a href="https://instagram.com/dineflow" target="_blank" class="social-btn instagram" title="Instagram">
                  <i class="fab fa-instagram"></i>
               </a>
               <a href="https://facebook.com/dineflow" target="_blank" class="social-btn facebook" title="Facebook">
                  <i class="fab fa-facebook-f"></i>
               </a>
               <a href="https://twitter.com/dineflow" target="_blank" class="social-btn twitter" title="Twitter">
                  <i class="fab fa-twitter"></i>
               </a>
               <a href="https://wa.me/919876543210" target="_blank" class="social-btn whatsapp" title="WhatsApp">
                  <i class="fab fa-whatsapp"></i>
               </a>
            </div>
         </div>

         <!-- Quick Links -->
         <div class="footer-col">
            <h4><i class="fas fa-link"></i> Quick Links</h4>
            <ul class="footer-links">
               <?php
               $role = '';
               if(isset($_SESSION['waiter_id'])) $role = 'waiter';
               elseif(isset($_SESSION['cook_id'])) $role = 'cook';
               elseif(isset($_SESSION['admin_id'])) $role = 'admin';
               if($role == 'waiter'):
               ?>
               <li><a href="waiter_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
               <li><a href="waiter_tables.php"><i class="fas fa-chair"></i> Tables</a></li>
               <li><a href="waiter_waiting.php"><i class="fas fa-clock"></i> Waiting Area</a></li>
               <li><a href="waiter_parcel.php"><i class="fas fa-box"></i> Parcels</a></li>
               <li><a href="waiter_menu.php"><i class="fas fa-book-open"></i> Menu</a></li>
               <li><a href="waiter_issue.php"><i class="fas fa-flag"></i> Report Issue</a></li>
               <li><a href="waiter_profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
               <?php elseif($role == 'cook'): ?>
               <li><a href="cook_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
               <li><a href="cook_tables.php"><i class="fas fa-chair"></i> Table Orders</a></li>
               <li><a href="cook_parcel.php"><i class="fas fa-box"></i> Parcel Orders</a></li>
               <li><a href="cook_menu.php"><i class="fas fa-book-open"></i> Menu</a></li>
               <li><a href="cook_report.php"><i class="fas fa-flag"></i> Report Issue</a></li>
               <?php elseif($role == 'admin'): ?>
               <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
               <li><a href="admin_waiters.php"><i class="fas fa-users"></i> Waiters</a></li>
               <li><a href="admin_menu.php"><i class="fas fa-book-open"></i> Menu</a></li>
               <li><a href="admin_tables.php"><i class="fas fa-chair"></i> Tables</a></li>
               <li><a href="admin_table_orders.php"><i class="fas fa-receipt"></i> Table Orders</a></li>
               <li><a href="admin_parcel_orders.php"><i class="fas fa-box"></i> Parcel Orders</a></li>
               <li><a href="admin_reports.php"><i class="fas fa-flag"></i> Reports</a></li>
               <?php endif; ?>
            </ul>
         </div>

         <!-- Contact Info -->
         <div class="footer-col">
            <h4><i class="fas fa-headset"></i> Contact Us</h4>
            <ul class="footer-contact">
               <li>
                  <i class="fas fa-map-marker-alt"></i>
                  <span>123, Food Street, Near City Mall,<br>Vadodara, Gujarat — 390001</span>
               </li>
               <li>
                  <i class="fas fa-phone-alt"></i>
                  <a href="tel:+919876543210">+91 98765 43210</a>
               </li>
               <li>
                  <i class="fas fa-phone-alt"></i>
                  <a href="tel:+919876543211">+91 98765 43211</a>
               </li>
               <li>
                  <i class="fas fa-envelope"></i>
                  <a href="mailto:info@dineflow.app">info@dineflow.app</a>
               </li>
               <li>
                  <i class="fab fa-instagram"></i>
                  <a href="https://instagram.com/dineflow" target="_blank">@dineflow</a>
               </li>
            </ul>
         </div>

         <!-- Hours -->
         <div class="footer-col">
            <h4><i class="fas fa-clock"></i> Working Hours</h4>
            <ul class="footer-hours">
               <li><span>Monday – Friday</span><b>10:00 AM – 11:00 PM</b></li>
               <li><span>Saturday</span><b>9:00 AM – 11:30 PM</b></li>
               <li><span>Sunday</span><b>10:00 AM – 10:00 PM</b></li>
            </ul>
            <div class="footer-status open">
               <i class="fas fa-circle"></i> We're Open Now!
            </div>
            <div class="footer-version">
               <i class="fas fa-code-branch"></i> System v1.0 &nbsp;|&nbsp; PHP + MySQL
            </div>
         </div>

      </div>
   </div>

   <!-- Footer Bottom Bar -->
   <div class="footer-bottom">
      <div class="footer-bottom-inner">
         <p>&copy; <?php echo date('Y'); ?> <strong>DineFlow</strong>. All rights reserved. Built with <i class="fas fa-heart" style="color:#ef4444;"></i> for restaurants.</p>
         <div class="footer-bottom-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Use</a>
            <a href="#">Support</a>
         </div>
      </div>
   </div>
</footer>

<?php 
if(isset($_SESSION['waiter_id'])){
include_once 'ai_widget.php'; 
}

if(isset($_SESSION['admin_id'])){
   
      include_once 'admin_ai_widget.php'; 
       
   }

if(isset($_SESSION['cook_id'])){
   
      include_once 'cook_ai_widget.php'; 
       
   }
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/script.js"></script>
