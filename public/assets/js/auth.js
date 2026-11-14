(function () {
    const showFeedback = function (el, type, messages) {
        if (!el) {
            return;
        }
        el.className = 'form-feedback is-' + type;
        el.textContent = '';

        if (Array.isArray(messages)) {
            const list = document.createElement('ul');
            list.style.margin = '0';
            list.style.paddingLeft = '1.25rem';
            messages.forEach(function (msg) {
                const li = document.createElement('li');
                li.textContent = msg;
                list.appendChild(li);
            });
            el.appendChild(list);
        } else {
            el.textContent = messages;
        }
        el.hidden = false;
    };

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        const feedback = document.getElementById('login-feedback');
        const submitBtn = document.getElementById('login-submit');

        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            const payload = {
                email: loginForm.email.value.trim(),
                password: loginForm.password.value
            };

            fetch('/api/auth/login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(function (response) {
                    return response.json().then(function (body) {
                        return { status: response.status, body: body };
                    });
                })
                .then(function (res) {
                    if (res.status >= 200 && res.status < 300) {
                        showFeedback(feedback, 'success', 'Berhasil masuk. Mengarahkan ke beranda Portal BERSATU...');
                        window.location.href = '/';
                        return;
                    }

                    if (res.body && res.body.data && res.body.data.errors) {
                        showFeedback(feedback, 'error', Object.values(res.body.data.errors));
                    } else if (res.body && res.body.error && res.body.error.description) {
                        const style = res.status === 403 ? 'warning' : 'error';
                        showFeedback(feedback, style, res.body.error.description);
                    } else {
                        showFeedback(feedback, 'error', 'Gagal masuk. Periksa kembali email dan kata sandi Anda.');
                    }

                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                })
                .catch(function () {
                    showFeedback(feedback, 'error', 'Koneksi terputus saat mencoba masuk. Periksa jaringan Anda.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                });
        });
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        const roleSelect = document.getElementById('reg-role');
        const instansiGroup = document.getElementById('reg-instansi-group');
        const instansiInput = document.getElementById('reg-instansi');
        const nipInput = document.getElementById('reg-nip-nik');
        const feedback = document.getElementById('register-feedback');
        const submitBtn = document.getElementById('register-submit');

        const syncRoleRequirements = function () {
            if (!roleSelect || !instansiGroup || !instansiInput || !nipInput) {
                return;
            }
            const isMasyarakat = roleSelect.value === 'masyarakat';
            instansiGroup.hidden = isMasyarakat;
            instansiInput.required = !isMasyarakat;
            nipInput.pattern = isMasyarakat ? '\\d{16}' : '(\\d{16}|\\d{18})';
        };

        syncRoleRequirements();
        if (roleSelect) {
            roleSelect.addEventListener('change', syncRoleRequirements);
        }

        registerForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            const payload = {
                role: registerForm.role.value,
                nama_lengkap: registerForm.nama_lengkap.value.trim(),
                instansi: registerForm.role.value === 'masyarakat' ? '' : registerForm.instansi.value.trim(),
                nip_nik: registerForm.nip_nik.value.trim(),
                wilayah_id: parseInt(registerForm.wilayah_id.value, 10) || 0,
                no_wa: registerForm.no_wa.value.trim(),
                email: registerForm.email.value.trim(),
                password: registerForm.password.value
            };

            fetch('/api/auth/register', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(function (response) {
                    return response.json().then(function (body) {
                        return { status: response.status, body: body };
                    });
                })
                .then(function (res) {
                    if (res.status === 201) {
                        const pesan = (res.body && res.body.data && res.body.data.pesan)
                            ? res.body.data.pesan
                            : 'Pendaftaran diterima. Akun berstatus menunggu_verifikasi hingga disetujui superadmin.';
                        showFeedback(feedback, 'success', pesan);
                        registerForm.reset();
                        syncRoleRequirements();
                    } else if (res.body && res.body.data && res.body.data.errors) {
                        showFeedback(feedback, 'error', Object.values(res.body.data.errors));
                    } else if (res.body && res.body.error && res.body.error.description) {
                        showFeedback(feedback, 'error', res.body.error.description);
                    } else {
                        showFeedback(feedback, 'error', 'Pendaftaran gagal. Periksa kembali isian formulir Anda.');
                    }

                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                })
                .catch(function () {
                    showFeedback(feedback, 'error', 'Koneksi terputus saat mengirim pendaftaran.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                });
        });
    }
})();
