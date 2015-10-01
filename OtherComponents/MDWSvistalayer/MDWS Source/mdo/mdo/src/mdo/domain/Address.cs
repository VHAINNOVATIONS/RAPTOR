using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Address
    {
        public string ValidFrom { get; set; }
        public string ValidTo { get; set; }
        public string Country { get; set; }
        public string Description { get; set; }
        string street1;
        string street2;
        string street3;
        string city;
        string county;
        string state;
        string zip;

        public string Street1
        {
            get { return street1; }
            set { street1 = value; }
        }


        public string Street2
        {
            get { return street2; }
            set { street2 = value; }
        }

        public string Street3
        {
            get { return street3; }
            set { street3 = value; }
        }

        public string City
        {
            get { return city; }
            set { city = value; }
        }

        public string County
        {
            get { return county; }
            set { county = value; }
        }

        public string State
        {
            get { return state; }
            set { state = value; }
        }

        public string Zipcode
        {
            get { return zip; }
            set { zip = value; }
        }

        /// <summary>
        /// A CRLF delimited string of address fields
        /// </summary>
        /// <returns>
        /// Street1
        /// Street2
        /// Street3
        /// City
        /// County
        /// State
        /// Zipcode
        /// </returns>
        public override string ToString()
        {
            StringBuilder sb = new StringBuilder();
            sb.Append(street1);
            sb.Append(Environment.NewLine);
            sb.Append(street2);
            sb.Append(Environment.NewLine);
            sb.Append(street3);
            sb.Append(Environment.NewLine);
            sb.Append(city);
            sb.Append(Environment.NewLine);
            sb.Append(county);
            sb.Append(Environment.NewLine);
            sb.Append(state);
            sb.Append(Environment.NewLine);
            sb.Append(zip);
            return sb.ToString();
        }

        /// <summary>
        /// Call ToString() on both objects and verify equality
        /// </summary>
        /// <param name="obj">Address</param>
        /// <returns>True if address strings are equal</returns>
        public override bool Equals(object obj)
        {
            if (!(obj is Address))
            {
                return false;
            }
            Address temp = obj as Address;
            if (String.Equals(temp.ToString(), this.ToString(), StringComparison.CurrentCultureIgnoreCase))
            {
                return true;
            }
            return false;
        }

        /// <summary>
        /// Calls ToString().GetHashCode() to retrieve the hashcode of the string representation of an Address object
        /// </summary>
        /// <returns>Int32 HashCode</returns>
        public override int GetHashCode()
        {
            return this.ToString().GetHashCode();
        }
    }
}
