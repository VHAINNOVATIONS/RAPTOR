using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class MhvPatient : BaseModel
    {
        private string _userName;

        protected string UserName
        {
            get { return _userName; }
            set { _userName = value; }
        }
        private string _lastName;

        protected string LastName
        {
            get { return _lastName; }
            set { _lastName = value; }
        }
        private string _firstName;

        protected string FirstName
        {
            get { return _firstName; }
            set { _firstName = value; }
        }
        private string _middleName;

        protected string MiddleName
        {
            get { return _middleName; }
            set { _middleName = value; }
        }

        private string _email;

        protected string Email
        {
            get { return _email; }
            set { _email = value; }
        }
        private string _ssn;

        protected string Ssn
        {
            get { return _ssn; }
            set { _ssn = value; }
        }
        private string _nssn;

        protected string Nssn
        {
            get { return _nssn; }
            set { _nssn = value; }
        }

        private DateTime _dob;

        protected DateTime Dob
        {
            get { return _dob; }
            set { _dob = value; }
        }
        private string _icn;

        protected string Icn
        {
            get { return _icn; }
            set { _icn = value; }
        }

        private string _facility;

        protected string Facility
        {
            get { return _facility; }
            set { _facility = value; }
        }
    }
}
