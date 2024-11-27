<!-- Footer -->
<footer class="footer mt-5">
    <div class="container-fluid bg-dark text-light py-4">
        <div class="row">
            <div class="col-md-4">
                <h5>Tupi National High School Library</h5>
                <p>
                    <i class="fas fa-map-marker-alt"></i> Tupi, South Cotabato<br>
                    <i class="fas fa-phone"></i> (123) 456-7890<br>
                    <i class="fas fa-envelope"></i> library@tupinhs.edu.ph
                </p>
            </div>
            <div class="col-md-4">
                <h5>Library Hours</h5>
                <p>
                    Monday - Friday: 7:00 AM - 5:00 PM<br>
                    Saturday: 8:00 AM - 12:00 PM<br>
                    Sunday: Closed
                </p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-light"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="books.php" class="text-light"><i class="fas fa-book"></i> Books</a></li>
                    <li><a href="students.php" class="text-light"><i class="fas fa-user-graduate"></i> Students</a></li>
                    <li><a href="alerts.php" class="text-light"><i class="fas fa-bell"></i> Alerts</a></li>
                </ul>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <hr class="bg-light">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> Tupi National High School Library Book Borrowing System. 
                    All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add this style to your CSS -->
<style>
    .footer {
        margin-top: auto;
        background-color: #343a40;
    }
    
    .footer h5 {
        color: #fff;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .footer a {
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .footer a:hover {
        color: #17a2b8 !important;
    }
    
    .footer i {
        margin-right: 8px;
    }
    
    .footer hr {
        opacity: 0.2;
        margin: 15px 0;
    }
    
    .footer ul li {
        margin-bottom: 10px;
    }
</style>

</body>
</html>