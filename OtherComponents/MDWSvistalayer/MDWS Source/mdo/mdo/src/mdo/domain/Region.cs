using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Region
    {
        String name;
        int id;
        ArrayList sites;

        public Region() {}

        public String Name
        {
            get
            {
                return name;
            }
            set
            {
                name = value;
            }
        }

        public int Id
        {
            get
            {
                return id;
            }
            set
            {
                id = value;
            }
        }

        public ArrayList Sites
        {
            get
            {
                return sites;
            }
            set
            {
                sites = value;
            }
        }
    }
}
