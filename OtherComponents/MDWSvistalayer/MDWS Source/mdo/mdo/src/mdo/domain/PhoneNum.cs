using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo
{
    public class PhoneNum
    {
        string areaCode;
        string exchange;
        string number;
        string description;

        public PhoneNum() { }

        public PhoneNum(string areaCode, string exchange, string number)
        {
            AreaCode = areaCode;
            Exchange = exchange;
            Number = number;
        }

        public PhoneNum(string phoneNum)
        {
            string digits = "";
            for (int i = 0; i < phoneNum.Length; i++)
            {
                if (StringUtils.isNumericChar(phoneNum[i]))
                {
                    digits += phoneNum[i];
                }
            }
            if (digits.Length != 7 && digits.Length != 10)
            {
                return;
            }
            if (digits.Length == 10)
            {
                AreaCode = digits.Substring(0, 3);
                Exchange = digits.Substring(3, 3);
                Number = digits.Substring(6);
            }
            else
            {
                AreaCode = "";
                Exchange = digits.Substring(0, 3);
                Number = digits.Substring(3);
            }
        }

        public string AreaCode
        {
            get { return areaCode; }
            set { areaCode = value; }
        }

        public string Exchange
        {
            get { return exchange; }
            set { exchange = value; }
        }

        public string Number
        {
            get { return number; }
            set { number = value; }
        }

        public string Description
        {
            get { return description; }
            set { description = value; }
        }

        /// <summary>
        /// Asserts all parts of phone number are equal
        /// </summary>
        /// <param name="obj">PhoneNum</param>
        /// <returns>True if all phone number pieces are equal</returns>
        public override bool Equals(object obj)
        {
            if (!(obj is PhoneNum))
            {
                return false;
            }
            PhoneNum temp = obj as PhoneNum;
            if (temp.AreaCode == this.areaCode && temp.Exchange == this.exchange && temp.Number == this.number)
            {
                return true;
            }
            return false;
        }

        /// <summary>
        /// Area Code + Exchange + Number
        /// </summary>
        /// <returns>Area Code + Exchange + Number with no spaces (e.g. 7347697100)</returns>
        public override string ToString()
        {
            return (areaCode + exchange + number);
        }

        /// <summary>
        /// Calls ToString().GetHashCode() to retrieve the hashcode of the string representation of a PhoneNum object
        /// </summary>
        /// <returns>Int32 HashCode</returns>
        public override int GetHashCode()
        {
            return this.ToString().GetHashCode();
        }
    }
}
