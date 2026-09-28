<!-- ══════════════ SOFT FOOTER (Theme B — full width, matches login) ══════════════ -->
<footer class="df-footer">
   <div class="df-footer-top">
      <div class="df-footer-wrap">

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
   </div>

   <div class="df-footer-bottom">
      <div class="df-footer-wrap df-footer-bottom-inner">
         <p>&copy; <?php echo date('Y'); ?> <strong>DineFlow</strong>. All rights reserved.</p>
         <div class="df-footer-bottom-links">
            <a href="#">Privacy Policy</a>
            <span>·</span>
            <a href="#">Terms of Use</a>
            <span>·</span>
            <a href="#">Support</a>
         </div>
      </div>
   </div>
</footer>

<style>
/* ===== Soft full-width footer (matches login theme) ===== */
.df-footer {
   margin-top: 3.5rem;
   width: 100%;
   background: #FFFCFA;
   border-top: 1px solid rgba(224,214,201,.7);
}

.df-footer-top {
   width: 100%;
   padding: 3rem 0 2.4rem;
   background: #FFFCFA;
}

.df-footer-wrap {
   width: 100%;
   max-width: 1280px;
   margin: 0 auto;
   padding: 0 2.8rem;
   box-sizing: border-box;
}

.df-footer-grid {
   display: grid;
   grid-template-columns: 1.35fr 1fr 1.2fr 1.15fr;
   gap: 2.8rem 3rem;
   align-items: start;
}

.df-footer-logo img {
   height: 3.8rem;
   width: auto;
   display: block;
   margin-bottom: .85rem;
}

.df-footer-tagline {
   font-size: 1.35rem;
   color: #7A6F66;
   line-height: 1.55;
   margin: 0 0 1.4rem;
   max-width: 28rem;
}

.df-footer-social {
   display: flex;
   gap: .65rem;
}

.df-footer-social a {
   width: 3.6rem;
   height: 3.6rem;
   border-radius: 50%;
   background: #F0EBE4;
   color: #A86B62;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 1.4rem;
   transition: .15s ease;
   text-decoration: none;
}

.df-footer-social a:hover {
   background: #A86B62;
   color: #fff;
   transform: translateY(-2px);
   box-shadow: 0 4px 12px rgba(168,107,98,.25);
}

.df-footer-col h4 {
   font-family: 'Source Serif 4', Georgia, serif;
   font-size: 1.6rem;
   font-weight: 700;
   color: #2A2420;
   margin: 0 0 1.2rem;
   padding-bottom: .5rem;
   position: relative;
}

.df-footer-col h4::after {
   content: '';
   position: absolute;
   left: 0;
   bottom: 0;
   width: 2.8rem;
   height: 2.5px;
   background: #A86B62;
   border-radius: 2px;
}

.df-footer-col ul {
   list-style: none;
   margin: 0;
   padding: 0;
}

.df-footer-col ul li {
   margin-bottom: .6rem;
}

.df-footer-col ul a {
   font-size: 1.35rem;
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
   gap: .75rem;
   font-size: 1.32rem;
   color: #6B5F54;
   margin-bottom: .75rem;
}

.df-footer-contact i {
   color: #A86B62;
   margin-top: .25rem;
   width: 1.5rem;
   text-align: center;
   flex-shrink: 0;
   font-size: 1.25rem;
}

.df-footer-contact a {
   color: #6B5F54;
   text-decoration: none;
}

.df-footer-contact a:hover { color: #A86B62; }

.df-footer-hours li {
   justify-content: space-between;
   gap: 1.2rem;
}

.df-footer-hours span { color: #7A6F66; }
.df-footer-hours b {
   font-weight: 700;
   color: #3A322C;
   white-space: nowrap;
}

.df-footer-open {
   margin-top: 1.1rem;
   display: inline-flex;
   align-items: center;
   gap: .5rem;
   font-size: 1.28rem;
   font-weight: 700;
   color: #4A6B4E;
   background: #E8F0E9;
   padding: .45rem 1.15rem;
   border-radius: 999px;
}

.df-footer-open i {
   font-size: .65rem;
   animation: df-pulse 1.6s ease infinite;
}

@keyframes df-pulse {
   0%, 100% { opacity: 1; }
   50% { opacity: .35; }
}

/* Full-width bottom bar */
.df-footer-bottom {
   width: 100%;
   background: #F3F0EB;
   border-top: 1px solid rgba(224,214,201,.65);
   padding: 1.35rem 0;
}

.df-footer-bottom-inner {
   display: flex;
   align-items: center;
   justify-content: space-between;
   flex-wrap: wrap;
   gap: .9rem;
   font-size: 1.28rem;
   color: #8A7F74;
}

.df-footer-bottom strong { color: #3A322C; }

.df-footer-bottom-links {
   display: flex;
   align-items: center;
   gap: .75rem;
}

.df-footer-bottom-links a {
   color: #8A7F74;
   text-decoration: none;
}

.df-footer-bottom-links a:hover { color: #A86B62; }
.df-footer-bottom-links span { opacity: .45; }

@media (max-width: 1000px) {
   .df-footer-grid {
      grid-template-columns: 1fr 1fr;
      gap: 2.4rem 2.5rem;
   }
   .df-footer-brand { grid-column: 1 / -1; }
   .df-footer-wrap { padding: 0 2rem; }
}

@media (max-width: 600px) {
   .df-footer-top { padding: 2.2rem 0 1.8rem; }
   .df-footer-wrap { padding: 0 1.5rem; }
   .df-footer-grid {
      grid-template-columns: 1fr;
      gap: 2rem;
   }
   .df-footer-bottom-inner {
      flex-direction: column;
      text-align: center;
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
