using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo
{
    public class Person
    {
        public String Religion { get; set; }
        public String Description { get; set; }
        public String Occupation { get; set; }
        public String EmploymentStatus { get; set; }
        public IList<Person> Relationships { get; set; }
        string id;
        PersonName name;
        SocSecNum ssn;
        string gender;
        string dob;
        string ethnicity;
        int age;
        string maritalStatus;
        Address homeAddress;
        PhoneNum homePhone;
        PhoneNum cellPhone;
        string email;
        Dictionary<string, DemographicSet> demographics;

        public Person() {}

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public PersonName Name
        {
            get { return name; }
            set { name = value; }
        }

        public void setName(String value)
        {
            name = new PersonName(value);
        }

        public void setSSN(String value)
        {
            if (StringUtils.isEmpty(value))
            {
                throw new ArgumentNullException("Empty SSN");
            }
            ssn = new SocSecNum(value);
        }

        public SocSecNum SSN
        {
            get
            {
                return ssn;
            }
            set
            {
                ssn = value;
            }
        }

        public string Gender
        {
            get
            {
                return gender;
            }
            set
            {
                //gender = conformGender(value);
                gender = value;
            }
        }

        private string conformGender(String s)
        {
            if (StringUtils.isEmpty(s))
            {
                return "";
            }
            string myGender = s.ToUpper();
            if (myGender.StartsWith("M"))
            {
                return "M";
            }
            else if (myGender.StartsWith("F"))
            {
                return "F";
            }
            else
            {
                throw new ArgumentException("Invalid gender");
            }
        }

        public string DOB
        {
            get
            {
                return dob;
            }
            set
            {
                dob = value;
            }
        }

        public string Ethnicity
        {
            get
            {
                return ethnicity;
            }
            set
            {
                ethnicity = value;
            }
        }

        public int Age
        {
            get { return age; }
            set { age = value; }
        }

        public string MaritalStatus
        {
            get { return maritalStatus; }
            set { maritalStatus = value; }
        }

        public Address HomeAddress
        {
            get { return homeAddress; }
            set { homeAddress = value; }
        }

        public PhoneNum HomePhone
        {
            get { return homePhone; }
            set { homePhone = value; }
        }

        public PhoneNum CellPhone
        {
            get { return cellPhone; }
            set { cellPhone = value; }
        }

        public string EmailAddress
        {
            get { return email; }
            set { email = value; }
        }

        public Dictionary<string, DemographicSet> Demographics
        {
            get { return demographics; }
            set { demographics = value; }
        }
    }
}
