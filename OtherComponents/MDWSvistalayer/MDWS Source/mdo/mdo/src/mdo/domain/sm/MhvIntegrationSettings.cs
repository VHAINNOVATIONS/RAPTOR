using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class MhvIntegrationSettings
    {
        private string _encryptionPassword;

        public string EncryptionPassword
        {
            get { return _encryptionPassword; }
            set { _encryptionPassword = value; }
        }
        private string _seed;

        public string Seed
        {
            get { return _seed; }
            set { _seed = value; }
        }
        private bool _expiration = false;

        public bool Expiration
        {
            get { return _expiration; }
            set { _expiration = value; }
        }
        private bool _productionMode = true;

        public bool ProductionMode
        {
            get { return _productionMode; }
            set { _productionMode = value; }
        }
        private string _patientSource;

        public string PatientSource
        {
            get { return _patientSource; }
            set { _patientSource = value; }
        }
        private string _administratorSource;

        public string AdministratorSource
        {
            get { return _administratorSource; }
            set { _administratorSource = value; }
        }
        private string _clinicianSource;

        public string ClinicianSource
        {
            get { return _clinicianSource; }
            set { _clinicianSource = value; }
        }
        private int _credentialsExpirationPeriod = 120;

        public int CredentialsExpirationPeriod
        {
            get { return _credentialsExpirationPeriod; }
            set { _credentialsExpirationPeriod = value; }
        }
        private string _authenticationKey;

        public string AuthenticationKey
        {
            get { return _authenticationKey; }
            set { _authenticationKey = value; }
        }


    }
}
