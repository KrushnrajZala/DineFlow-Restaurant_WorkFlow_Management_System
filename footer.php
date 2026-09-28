<!-- ══════════════ SOFT FOOTER (Theme B — matches login) ══════════════ -->
<footer class="df-footer">
   <div class="df-footer-inner">

      <div class="df-footer-grid">

         <!-- Brand -->
         <div class="df-footer-brand">
            <a href="<?php
               if(isset($_SESSION['waiter_id'])) echo 'waiter_dashboard.php';
               elseif(isset($_SESSION['cook_id'])) echo 'cook_dashboard.php';
               elseif(isset($_SESSION['admin_id'])) echo 'admin_dashboard.php';
               else echo 'index.php';
            ?>" class="df-footer-logo">
               <img src="images/logo_user.png" alt="DineFlow">
            </a>
            <p class="df-footer-tagline">Smart restaurant management, made simple.</p>
            <div class="df-footer-social">
               <a href="https://instagram.com/dineflow" target="_blank" rel="noopener" title="Instagram"><i class="fab fa-instagram"></i></a>
               <a href="https://facebook.com/dineflow" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i></a>
               <a href="https://twitter.com/dineflow" target="_blank" rel="noopener" title="Twitter"><i class="fab fa-twitter"></i></a>
               <a href="https://wa.me/919876543210" target="_blank" rel="noopener" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
         </div>

         <!-- Quick Links -->
         <div class="df-footer-col">
            <h4>Quick Links</h4>
            <ul>
               <?php
               $role = '';
               if(isset($_SESSION['waiter_id'])) $role = 'waiter';
               elseif(isset($_SESSION['cook_id'])) $role = 'cook';
               elseif(isset($_SESSION['admin_id'])) $role = 'admin';
               if($role == 'waiter'):
               ?>
               <li><a href="waiter_dashboard.php">Dashboard</a></li>
               <li><a href="waiter_tables.php">Tables</a></li>
               <li><a href="waiter_waiting.php">Waiting Area</a></li>
               <li><a href="waiter_parcel.php">Parcels</a></li>
               <li><a href="waiter_menu.php">Menu</a></li>
               <li><a href="waiter_profile.php">Profile</a></li>
               <?php elseif($role == 'cook'): ?>
               <li><a href="cook_dashboard.php">Dashboard</a></li>
               <li><a href="cook_tables.php">Table Orders</a></li>
               <li><a href="cook_parcel.php">Parcel Orders</a></li>
               <li><a href="cook_menu.php">Menu</a></li>
               <li><a href="cook_report.php">Report Issue</a></li>
               <?php elseif($role == 'admin'): ?>
               <li><a href="admin_dashboard.php">Dashboard</a></li>
               <li><a href="admin_waiters.php">Waiters</a></li>
               <li><a href="admin_menu.php">Menu</a></li>
               <li><a href="admin_tables.php">Tables</a></li>
               <li><a href="admin_table_orders.php">Table Orders</a></li>
               <li><a href="admin_parcel_orders.php">Parcel Orders</a></li>
               <li><a href="admin_reports.php">Reports</a></li>
               <?php else: ?>
               <li><a href="index.php">Home</a></li>
               <li><a href="waiter_login.php">Waiter Login</a></li>
               <li><a href="cook_login.php">Cook Login</a></li>
               <li><a href="admin_login.php">Admin Login</a></li>
               <?php endif; ?>
            </ul>
         </div>

         <!-- Contact -->
         <div class="df-footer-col">
            <h4>Contact Us</h4>
            <ul class="df-footer-contact">
               <li>
                  <i class="fas fa-map-marker-alt"></i>
                  <span>123, Food Street, Near City Mall,<br>Vadodara, Gujarat — 390001</span>
               </li>
               <li>
                  <i class="fas fa-phone-alt"></i>
                  <a href="tel:+919876543210">+91 98765 43210</a>
               </li>
               <li>
                  <i class="fas fa-envelope"></i>
                  <a href="mailto:info@dineflow.app">info@dineflow.app</a>
               </li>
            </ul>
         </div>

         <!-- Hours -->
         <div class="df-footer-col">
            <h4>Working Hours</h4>
            <ul class="df-footer-hours">
               <li><span>Mon – Fri</span><b>10:00 AM – 11:00 PM</b></li>
               <li><span>Saturday</span><b>9:00 AM – 11:30 PM</b></li>
               <li><span>Sunday</span><b>10:00 AM – 10:00 PM</b></li>
            </ul>
            <div class="df-footer-open">
               <i class="fas fa-circle"></i> We're Open Now
            </div>
         </div>

      </div>
   </div>

   <div class="df-footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> <strong>DineFlow</strong>. All rights reserved.</p>
      <div class="df-footer-bottom-links">
         <a href="#">Privacy Policy</a>
         <span>·</span>
         <a href="#">Terms of Use</a>
         <span>·</span>
         <a href="#">Support</a>
      </div>
   </div>
</footer>

<style>
/* ===== Theme B Soft Footer (matches login) ===== */
.df-footer {
   margin-top: 3.5rem;
   background: #F3F0EB;
   padding: 0 1.6rem 0;
}
.df-footer-inner {
   max-width: 1180px;
   margin: 0 auto;
   background: #FFFCFA;
   border: 1px solid rgba(224,214,201,.6);
   border-radius: 18px 18px 0 0;
   box-shadow: 0 -4px 24px rgba(40,32,26,.05);
   padding: 2.8rem 2.6rem 2.2rem;
}
.df-footer-grid {
   display: grid;
   grid-template-columns: 1.4fr 1fr 1.15fr 1.1fr;
   gap: 2.4rem;
}
.df-footer-logo img {
   height: 3.6rem;
   width: auto;
   display: block;
   margin-bottom: .7rem;
}
.df-footer-tagline {
   font-size: 1.3rem;
   color: #7A6F66;
   line-height: 1.5;
   margin: 0 0 1.3rem;
   max-width: 26rem;
}
.df-footer-social {
   display: flex;
   gap: .6rem;
}
.df-footer-social a {
   width: 3.4rem;
   height: 3.4rem;
   border-radius: 50%;
   background: #F0EBE4;
   color: #A86B62;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 1.35rem;
   transition: .15s ease;
}
.df-footer-social a:hover {
   background: #A86B62;
   color: #fff;
   transform: translateY(-2px);
}
.df-footer-col h4 {
   font-family: 'Source Serif 4', Georgia, serif;
   font-size: 1.55rem;
   font-weight: 700;
   color: #2A2420;
   margin: 0 0 1.1rem;
   padding-bottom: .45rem;
   border-bottom: 2px solid #E8DFD4;
   display: inline-block;
}
.df-footer-col ul {
   list-style: none;
   margin: 0;
   padding: 0;
}
.df-footer-col ul li {
   margin-bottom: .55rem;
}
.df-footer-col ul a {
   font-size: 1.3rem;
   color: #6B5F54;
   text-decoration: none;
   transition: color .15s;
}
.df-footer-col ul a:hover {
   color: #A86B62;
}
.df-footer-contact li,
.df-footer-hours li {
   display: flex;
   align-items: flex-start;
   gap: .7rem;
   font-size: 1.28rem;
   color: #6B5F54;
   margin-bottom: .7rem;
}
.df-footer-contact i {
   color: #A86B62;
   margin-top: .2rem;
   width: 1.4rem;
   text-align: center;
   flex-shrink: 0;
}
.df-footer-contact a {
   color: #6B5F54;
   text-decoration: none;
}
.df-footer-contact a:hover { color: #A86B62; }
.df-footer-hours li {
   justify-content: space-between;
   gap: 1rem;
}
.df-footer-hours span { color: #7A6F66; }
.df-footer-hours b {
   font-weight: 700;
   color: #3A322C;
   white-space: nowrap;
}
.df-footer-open {
   margin-top: 1rem;
   display: inline-flex;
   align-items: center;
   gap: .45rem;
   font-size: 1.25rem;
   font-weight: 700;
   color: #4A6B4E;
   background: #E8F0E9;
   padding: .4rem 1rem;
   border-radius: 999px;
}
.df-footer-open i {
   font-size: .7rem;
   animation: pulse-dot 1.6s ease infinite;
}
@keyframes pulse-dot {
   0%, 100% { opacity: 1; }
   50% { opacity: .35; }
}

.df-footer-bottom {
   max-width: 1180px;
   margin: 0 auto;
   background: #F8F4EF;
   border: 1px solid rgba(224,214,201,.5);
   border-top: none;
   border-radius: 0 0 14px 14px;
   padding: 1.2rem 2.6rem;
   display: flex;
   align-items: center;
   justify-content: space-between;
   flex-wrap: wrap;
   gap: .8rem;
   font-size: 1.25rem;
   color: #8A7F74;
}
.df-footer-bottom strong { color: #3A322C; }
.df-footer-bottom-links {
   display: flex;
   align-items: center;
   gap: .7rem;
}
.df-footer-bottom-links a {
   color: #8A7F74;
   text-decoration: none;
}
.df-footer-bottom-links a:hover { color: #A86B62; }
.df-footer-bottom-links span { opacity: .5; }

@media (max-width: 900px) {
   .df-footer-grid {
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
   }
   .df-footer-brand { grid-column: 1 / -1; }
}
@media (max-width: 560px) {
   .df-footer-inner { padding: 2rem 1.5rem 1.6rem; border-radius: 14px 14px 0 0; }
   .df-footer-grid { grid-template-columns: 1fr; gap: 1.8rem; }
   .df-footer-bottom {
      flex-direction: column;
      text-align: center;
      padding: 1.2rem 1.5rem;
      border-radius: 0 0 12px 12px;
   }
}
</style>

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
